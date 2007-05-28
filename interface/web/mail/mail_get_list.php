<?php
require_once('../../lib/config.inc.php');
require_once('../../lib/app.inc.php');

/******************************************
* Begin Form configuration
******************************************/

$list_def_file = "list/mail_get.list.php";

/******************************************
* End Form configuration
******************************************/

// Checking module permissions
if(!stristr($_SESSION["s"]["user"]["modules"],$_SESSION["s"]["module"]["name"])) {
	header("Location: ../index.php");
	exit;
}

$app->uses('listform_actions');

// Limit the results to alias domains
// $app->listform_actions->SQLExtWhere = "";

$app->listform_actions->onLoad();


?>