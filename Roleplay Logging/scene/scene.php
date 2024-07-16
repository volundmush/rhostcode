<?php
	require_once 'base.php';

	if (!$scenedb->count('scene_view', ['scene_id'=>$num]))
	{
		$smarty->assign('message', "The entered ID was not found.");
		$smarty->display('error.tpl');
	}
	else
	{
		$scene_data = $scenedb->get('scene_view', ['owner_name', 'owner_id', 'scene_title', 'scene_outcome',
		 'scene_status', 'scene_date_created'], ['scene_id'=>$num]);

		$pose_list = $scenedb->select('pose_view', ['entity_id', 'channel_category_name',
                         'display_name', 'pose_text'],
                        ['AND' => ['pose_is_deleted' => 0, 'scene_id' => $num]]);
		$pose_data = array();
		$log_data = "";
		$poser_ids = array();
		foreach ($pose_list as $indiv)
		{
			$scene_text = convertRhost($indiv['pose_text']);
			$scene_text = str_replace("\n", "<br>", $scene_text);
			$scene_text = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $scene_text);
			$scene_text = str_replace("  ", "&nbsp;&nbsp;", $scene_text);
			$pose_data[] = ["owner_id" => $indiv['entity_id'], "channel_category" => $indiv['channel_category_name'],
                        "display_name" => $indiv['display_name'], "has_owner" => !is_null($indiv['entity_id']) ? true : false,
                        "text" => $scene_text];
			$poser_ids[] = $indiv['entity_id'];
		}
		
		$poser_ids = array_unique($poser_ids);
		$poser_list = implode(", ",$poser_ids);
		$scene_date = substr($scene_data['scene_date_created'],0,10);

		$scene = ["title"=>convertRhost($scene_data['scene_title']), 'id'=>$num, 'description'=>convertRhost($scene_data['scene_outcome']), 'formatted_poses'=>$pose_data, 'poser_ids'=>$poser_list, 'creation_date'=>$scene_date];

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
