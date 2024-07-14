<?php
    require_once 'base.php';

    if (!$scenedb->count('scene_view', ['scene_id' => $num])) {
        $smarty->assign('message', "The entered ID was not found.");
        $smarty->display('error.tpl');
    } else {

    $pose_list = $scenedb->select('pose_view', ['display_name', 'entity_id', 'pose_text_color'], ['AND' => ['pose_is_deleted' => 0, 'scene_id' => $num]]);
    $pose_data = array();

    foreach ($pose_list as $indiv) {
        $scene_text = convertRhost($indiv['pose_text_color']);
        $scene_text = str_replace("\n", "<br>", $scene_text);
        $scene_text = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $scene_text);
        $scene_text = str_replace("  ", "&nbsp;&nbsp;", $scene_text);
        $pose_data[] = ["owner" => $indiv['entity_id'], "owner_name" => $indiv['display_name'], "text" => $scene_text];
    }
    
    if ($json) {
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($pose_data);
    } else {
        $smarty->assign('poses', $pose_data);
        $smarty->display('templates/poses.tpl');
    }
}


?>
