<?php
    require_once 'base.php';

    if (!$scenedb->count('scene_view', ['scene_id' => $num])) {
        $smarty->assign('message', "The entered ID was not found.");
        $smarty->display('error.tpl');
    } else {

    $pose_list = $scenedb->select('pose_view', ['entity_id', 'channel_category_name',
                 'channel_name', 'display_name', 'pose_text_color'],
                ['AND' => ['pose_is_deleted' => 0, 'scene_id' => $num]]);
    ['AND' => ['pose_is_deleted' => 0, 'scene_id' => $num]]);
    $pose_data = array();

    foreach ($pose_list as $indiv) {
        $scene_text = convertRhost($indiv['pose_text_color']);
        $scene_text = str_replace("\n", "<br>", $scene_text);
        $scene_text = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $scene_text);
        $scene_text = str_replace("  ", "&nbsp;&nbsp;", $scene_text);
        $pose_data[] = ["owner_id" => $indiv['entity_id'], "channel_category" => $indiv['channel_category_name'],
            "display_name" => $indiv['display_name'], "channel_name" => $indiv['channel_name'],
            "has_owner" => !is_null($indiv['entity_id']) ? true : false, "text" => $scene_text];
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
