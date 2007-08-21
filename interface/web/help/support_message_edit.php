<?php

//* Set the path to the form definition file.
$tform_def_file = "form/support_message.tform.php";

//* include the basic application and configuration files
require_once('../../lib/config.inc.php');
require_once('../../lib/app.inc.php');

//* Checking module permissions
if(!stristr($_SESSION["s"]["user"]["modules"],'help')) {
	header("Location: ../index.php");
	exit;
}

//* Loading the templating and form classes
$app->uses('tpl,tform,tform_actions');
$app->load('tform_actions');

//* Creating a class page_action that extends the tform_actions base class
class page_action extends tform_actions {

	//* Custom onSubmit Event handler
	function onSubmit() {
		global $app, $conf;
		
		//* If the current user is not the admin user
		if($_SESSION["s"]["user"]["typ"] != 'admin') {
			//* Set the admin as recipient
			$this->dataRecord["recipient_id"] = 1;
		}
		
		// Set the sender_id field to the ID of the current user
		$this->dataRecord["sender_id"] = $_SESSION["s"]["user"]["userid"];
		
		//* call the onSubmit function of the parent class
		parent::onSubmit();
	}
	
	//* Custom onShow Event handler
	function onShow() {
		global $app, $conf;

		//* We do not want that messages get edited, so we switch to a 
		//  read only template  if a existing message is loaded
		if($this->id > 0) {
			$app->tform->formDef['tabs']['message']['template'] = 'templates/support_message_view.htm';
		}
		
		//* call the onShow function of the parent class
		parent::onShow();
	}
}

//* Create the new page object
$page = new page_action();



//* Start the page rendering and action handling
$page->onLoad();

?>