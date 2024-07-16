<?php
	require_once 'base.php';

	if (!$scenedb->count('scene_view', ['owner_id'=>$num]))
	{
		$smarty->assign('message', "The entered ID was not found.");
		$smarty->display('templates/error.tpl');
	}
	else
	{
	$scene_list = array_reverse($scenedb->select('scene_view', ["scene_id", "owner_id", "owner_name",
	"scene_title", "scene_outcome", "scene_status"], ["scene_status[!]"=>1, "owner_id"=>$num]));
	
	$state_array = ["0"=>"Scheduled", "1"=>"Active", "2"=>"Paused", "3"=>"Finished"];
	$scene_data = array();
	foreach ($scene_list as $indiv)
	{
		$indiv_data = ["id"=>$indiv['scene_id'], "owner_name"=>$indiv['owner_name'], "owner"=>$indiv['owner_id'],
		'title'=>convertRhost($indiv['scene_title']), "description"=>convertRhost($indiv['scene_outcome']),
		 "state"=>$state_array[$indiv['scene_status']]];
		$scene_data[] = $indiv_data;
	}
	if($json) {
		header("Content-type: application/json");
		echo json_encode($scene_data);
	} else {
		$smarty->assign('scenes', $scene_data);
		$smarty->display('templates/listing.tpl');
	}

	}
	

?>
