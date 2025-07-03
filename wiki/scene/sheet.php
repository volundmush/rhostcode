<?php
    require_once 'game.php';

    if(!isset($_GET['objid'])) {
        sendJson(array());
        return;
    }

    if(!isset($_GET['info'])) {
        sendJson(array());
        return;
    }

    $results = getInfoFiles($_GET['info'], $_GET['objid']);

    sendJson($results);

?>