<?php
	require_once 'vendor/autoload.php';
	require_once 'ansi.php';
	use Medoo\Medoo;
	use Smarty\Smarty;

	$db_data = [
		"database_type"=>"mysql",
		"database_name"=>"database_name",
		"server"=>"localhost",
		"username"=>"username",
		"password"=>"password",
		"charset"=>"utf8",
		"prefix"=>""
	];

	$posecount = 0;
	$gamename = "SceneSys";
	$gamedesc = "Game desc here!";
	$gameurl = "https://github.com/volundmush/rhostcode";
	$gameconnect = "<connect data here>";
	
	$scenedb = new Medoo($db_data);
	$num = $_GET['id'] ?? false;
    $json = $_GET['json'] ?? false;
	$smarty = new Smarty();
	$gameinfo = ["name"=>$gamename,"desc"=>$gamedesc,"site"=>$gameurl,"connect"=>$gameconnect];
	$smarty->assign('info', $gameinfo);
	
?>
