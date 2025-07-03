<?php
    require_once 'game.php';

    header("Content-type: application/json; charset=utf-8");

    if(!isset($_GET['objid'])) {
        echo json_encode((object)[]);
        return;
    }

    $objid = $_GET['objid'];

    $data = getDescription($objid);

    echo json_encode($data);

?>