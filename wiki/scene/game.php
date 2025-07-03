<?php
    require_once 'gamedata.php';

    $auth_string = base64_encode($game_data['dbref'] . ':' . $game_data['password']);

    $api_url = "http://" . $game_data['hostname'] . ':' . $game_data['port'];

    $api_headers = [
        "Authorization: Basic " . $auth_string
    ];

    function sendJson($data) {
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
    }

    function execLua($code, $headers) {
        global $api_url;
        global $api_headers;

        $encoded = base64_encode($code);

        $full_headers = array_merge($api_headers, $headers);
        $full_headers[] = "X-Lua64: " . $encoded;
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $full_headers)
            ]
        ];

        $context = stream_context_create($opts);

        // 4) Fire off the request
        $result = @file_get_contents($api_url, false, $context);
        if ($result === false) {
            $err = error_get_last();
            throw new Exception("HTTP request failed: " . $err['message']);
        }

        return $result;
    }

    function isObjid($input) {
        $pattern = '/^#(\d+):(\d+)$/';
        return (bool) preg_match($pattern, $input);
    }

    function getInfoFilesHelper($info_obj, $target) {

        // this is apparently called a HEREDOC
        // Dumbest name I've heard so far for this.
        $code = <<<LUA
        local data = {}
        local target = "{$target}"

        local attrs = rhost.strfunc("u", "{$info_obj}/FN.LIST_ATTRS_ORDER,{$target}",",")

        for attr in string.gmatch(attrs, "%S+") do
          local info_file = {}
          local baseget = target .. "/" .. attr
          info_file.name = rhost.strfunc("get", baseget .. ".NAME")
          info_file.value = rhost.strfunc("get", baseget .. ".VALUE")
          info_file.body = rhost.strfunc("get", baseget .. ".BODY")
          info_file.summary = rhost.strfunc("get", baseget .. ".SUMMARY")

          table.insert(data, info_file)
        end

        return json.encode(data)

        LUA;

        try {
            $raw = execLua($code, array());
        } catch(Exception $e) {
            // some kind of error maybe?
            return [];
        }

        // Decode into a PHP array of associative arrays:
        $arr = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // invalid JSON? bail out
            return [];
        }

        // Example: iterate and mutate each element
        foreach ($arr as &$item) {
            if(isset($item['summary'])) {
                $item['summary'] = convertRhost($item['summary']);
            }
            $item['body'] = convertRhost($item['body']);
        }
        unset($item); // break the reference

        return $arr;
    }

    function getInfoFiles($info_type, $target) {
        global $info_data;

        if(!array_key_exists($info_type, $info_data)) {
            return [];
        }

        if(!isObjid($target)) {
            return [];
        }

        $info_obj = $info_data[$info_type];

        return getInfoFilesHelper($info_obj, $target);

    }

    function getDescription($target) {
        if(!isObjid($target)) {
            return (object)[];
        }

        $code = <<<LUA
            local data = {}
            local target = "{$target}"

            data["name"] = rhost.strfunc("name", target, "|")
            data["describe"] = rhost.strfunc("get", target .. "/" .. "DESCRIBE", "|")
            data["short"] = rhost.strfunc("get", target .. "/" .. "SHORT-DESC", "|")

            return json.encode(data)

        LUA;

        try {
            $raw = execLua($code, array());
        } catch(Exception $e) {
            return (object)[];
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return (object)[];
        }

        $renderables = ["describe", "short"];

        foreach ($data as $key => &$value) {
            if (in_array($key, $renderables, true)) {
                $value = convertRhost($value);
            }
        }
        unset($value);

        return $data;
    }

    function getActiveCharacters() {

        $code = <<<LUA
            local data = {}
            local characters = rhost.strfunc("u","#api/FN.ALL_APPROVED","|")

            for target in characters:gmatch("%S+") do
                local character = {}
                character["objid"] = target
                character["name"] = rhost.strfunc("name", target, "|")

                table.insert(data, character)
            end

            return json.encode(data)

        LUA;

        try {
            $raw = execLua($code, array());
        } catch(Exception $e) {
            // some kind of error maybe?
            return [];
        }

        // Decode into a PHP array of associative arrays:
        $arr = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // invalid JSON? bail out
            return [];
        }

        return $arr;

    }

    function getThemes(?string $category) {

        $code = <<<LUA
            local data = {}
            local themes = rhost.strfunc("u","#theme/FN.THEMES_APPROVED","|")

            for target in themes:gmatch("%S+") do
                local theme = {}

                theme["objid"] = target
                theme["name"] = rhost.strfunc("name", target, "|")
                theme["category"] = rhost.strfunc("default", target .. "/CATEGORY|Uncategorized", "|")

                table.insert(data, theme)
            end

            return json.encode(data)

        LUA;

        try {
            $raw = execLua($code, array());
        } catch(Exception $e) {
            // some kind of error maybe?
            return [];
        }

        // Decode into a PHP array of associative arrays:
        $arr = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // invalid JSON? bail out
            return [];
        }

        // If $category was passed (not null), filter out any non‐matching entries:
        if ($category !== null) {
            $arr = array_filter(
                $arr,
                function (array $item) use ($category) {
                    // strcasecmp returns 0 if the two strings are equal, ignoring case
                    return strcasecmp($item['category'], $category) === 0;
                }
            );
            // array_filter preserves original keys; reindex to [0,1,2,…] if you prefer:
            $arr = array_values($arr);
        }

        return $arr;

    }

?>