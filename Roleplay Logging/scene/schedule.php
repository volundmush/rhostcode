<?php 
	require_once 'base.php';
	$num = ($_REQUEST['id']  ? $_REQUEST['id'] : $num );
	
	if (!$scenedb->count('scene_view', ['scene_id'=>$num]))
	{
		$smarty->assign('message', "The entered ID was not found.");
		$smarty->display('templates/error.tpl');
	}
	else
	{
		
		$schedule_data = $scenedb->get('scene_view', ['owner_name', 'owner_id', 'scene_title_color',
		'scene_pitch_color', 'scene_date_scheduled'], ['scene_id'=>$num]);
		
		$truetime = explode(" ",$schedule_data['scene_date_scheduled']);
		$hourminute = explode(":",$truetime[1]);
		$scene_time = $truetime[0].":".$truetime[1];
		
		$schedule = ['player_name'=>$schedule_data['owner_name'],'date'=>$truetime[0],'id'=>$num,
		'title'=>convertRhost($schedule_data['scene_title_color']),'desc'=>$schedule_data['scene_pitch'],
		'time'=>$scene_time];
		
		$smarty->assign('schedule',$schedule);
		$smarty->display('templates/schedule.tpl');
	}
	
?>
