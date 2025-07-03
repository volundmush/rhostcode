<?php
    require_once 'info.php';

    header("Content-type: application/json; charset=utf-8");

    if(!isset($_GET['objid'])) {
        echo json_encode((object)[]);
        return;
    }

    $objid = $_GET['objid'];

    if(!isObjid($objid)) {
        echo json_encode((object)[]);
        return;
    }

    $code = <<<LUA

    local data = {}
    local target = "{$objid}"

    local fields = rhost.strfunc("get", "#finger/ALLFIELDS", "|")

    for field in string.gmatch(fields, "%S+") do
        local got = rhost.strfunc("u", "#finger/GETTER." .. field .. "|" .. target, "|")
        data[field:lower()] = got
    end

    data["describe"] = rhost.strfunc("get", target .. "/" .. "DESCRIBE", "|")
    data["short"] = rhost.strfunc("get", target .. "/" .. "SHORT-DESC", "|")

    return json.encode(data)

    LUA;

    try {
        $raw = execLua($code, array());
    } catch(Exception $e) {
        echo json_encode((object)[]);
        return;
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode((object)[]);
        return;
    }

    $renderables = ["skills", "profile", "quote", "describe", "short"];

    foreach ($data as $key => &$value) {
        if (in_array($key, $renderables, true)) {
            $value = convertRhost($value);
        }
    }
    unset($value);

    echo json_encode($data);

?>