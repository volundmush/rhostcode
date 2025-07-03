<?php
    require_once 'game.php';

    if(!isset($_GET['mode'])) {
        sendJson(array());
        return;
    }

    $mode = $_GET["mode"];

    $modes = ["characters", "themes", "factions"];

    if(!in_array($mode, $modes, true)) {
        sendJson(array());
        return;
    }

    $results = array();

    if($mode == "characters") {
        $results = getActiveCharacters();
    } else if($mode == "themes") {

        $category = $_GET['category'] ?? null;
        $results = getThemes($category);
    } else if($mode == "factions") {
        $results = getActiveFactions();
    }

    sendJson($results);

?>