<?php
	require_once 'base.php';

	if (!$scenedb->count('scene_view', ['scene_id'=>$num]))
	{
		$smarty->assign('message', "The entered ID was not found.");
		$smarty->display('error.tpl');
	}
	else
	{
		$scene_data = $scenedb->get('scene_view', ['owner_name', 'owner_id', 'scene_title_color', 'scene_outcome_color',
		 'scene_status', 'scene_date_created'], ['scene_id'=>$num]);

		$pose_list = $scenedb->select('pose_view', ['display_name', 'entity_id', 'pose_text_color'], ['AND'=>['pose_is_deleted'=>0, 'scene_id'=>$num]]);
		$pose_data = array();
		$log_data = "";
		$poser_ids = array();
		foreach ($pose_list as $indiv)
		{
			$scene_text = convertRhost($indiv['pose_text_color']);
			$scene_text = str_replace("\n", "<br>", $scene_text);
			$scene_text = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $scene_text);
			$scene_text = str_replace("  ", "&nbsp;&nbsp;", $scene_text);
			$pose_data[] = ["owner"=>$indiv['entity_id'], "owner_name"=>$indiv['display_name'], "text"=>$scene_text];
			$poser_ids[] = $indiv['entity_id'];
		}
		
		$poser_ids = array_unique($poser_ids);
		$poser_list = implode(", ",$poser_ids);
		$scene_date = substr($scene_data['scene_date_created'],0,10);

		$scene = ["title"=>convertRhost($scene_data['scene_title_color']), 'id'=>$num, 'description'=>convertRhost($scene_data['scene_outcome_color']), 'formatted_poses'=>$pose_data, 'poser_ids'=>$poser_list, 'creation_date'=>$scene_date];

		if($json) {
			header("Content-type: application/json");
			echo json_encode($scene);
		} else {
			$smarty->assign('poses', $pose_data);
			$smarty->assign('scene', $scene);
			$smarty->display('templates/scene.tpl');
		}
	}

?>
