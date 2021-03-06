<?php
/*
Copyright (c) 2007, Till Brehm, projektfarm Gmbh
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice,
      this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.
    * Neither the name of ISPConfig nor the names of its contributors
      may be used to endorse or promote products derived from this software without
      specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/


/******************************************
* Begin Form configuration
******************************************/

$tform_def_file = "form/directive_snippets.tform.php";

/******************************************
* End Form configuration
******************************************/

require_once '../../lib/config.inc.php';
require_once '../../lib/app.inc.php';

//* Check permissions for module
$app->auth->check_module_permissions('admin');

// Loading classes
$app->uses('tpl,tform,tform_actions');

class page_action extends tform_actions {

	function onShow() {
		global $app, $conf;
		
		if($this->id > 0){
			$record = $app->db->queryOneRecord("SELECT * FROM directive_snippets WHERE directive_snippets_id = ?", $this->id);
			if($record['master_directive_snippets_id'] > 0){
				unset($app->tform->formDef["tabs"]['directive_snippets']['fields']['name'], $app->tform->formDef["tabs"]['directive_snippets']['fields']['type'], $app->tform->formDef["tabs"]['directive_snippets']['fields']['snippet'], $app->tform->formDef["tabs"]['directive_snippets']['fields']['required_php_snippets']);
			}
			unset($record);
		}
		
		parent::onShow();
	}
	
	function onShowEnd() {
		global $app, $conf;
		
		$is_master = false;
		if($this->id > 0){
			if($this->dataRecord['master_directive_snippets_id'] > 0){
				$is_master = true;
				$app->tpl->setVar("name", $this->dataRecord['name'], true);
				$app->tpl->setVar("type", $this->dataRecord['type'], true);
				$app->tpl->setVar("snippet", $this->dataRecord['snippet'], true);
			}
		}

		$app->tpl->setVar("is_master", $is_master);
		
		parent::onShowEnd();
	}
	
	function onSubmit() {
		global $app, $conf;

		if($this->id > 0){
			$record = $app->db->queryOneRecord("SELECT * FROM directive_snippets WHERE directive_snippets_id = ?", $this->id);
			if($record['master_directive_snippets_id'] > 0){
				unset($app->tform->formDef["tabs"]['directive_snippets']['fields']['name'], $app->tform->formDef["tabs"]['directive_snippets']['fields']['type'], $app->tform->formDef["tabs"]['directive_snippets']['fields']['snippet'], $app->tform->formDef["tabs"]['directive_snippets']['fields']['required_php_snippets']);
			}
	
			if(isset($this->dataRecord['update_sites'])) {
				parent::onSubmit();
			} else {
				$app->db->query('UPDATE directive_snippets SET name = ?, type = ?, snippet = ?, customer_viewable = ?, required_php_snippets = ?, active = ? WHERE directive_snippets_id = ?', $this->dataRecord['name'], $this->dataRecord['type'], $this->dataRecord['snippet'], $this->dataRecord['customer_viewable'], implode(',', $this->dataRecord['required_php_snippets']), $this->dataRecord['active'], $this->id);

	            if($_REQUEST["next_tab"] == '') {
    	            $list_name = $_SESSION["s"]["form"]["return_to"];
                	if($list_name != '' && $_SESSION["s"]["list"][$list_name]["parent_name"] != $app->tform->formDef["name"]) {
                    	$redirect = "Location: ".$_SESSION["s"]["list"][$list_name]["parent_script"]."?id=".$_SESSION["s"]["list"][$list_name]["parent_id"]."&next_tab=".$_SESSION["s"]["list"][$list_name]["parent_tab"];
	                    $_SESSION["s"]["form"]["return_to"] = '';
    	                session_write_close();
        	            header($redirect);
                	} elseif (isset($_SESSION["s"]["form"]["return_to_url"]) && $_SESSION["s"]["form"]["return_to_url"] != '') {
	                    $redirect = $_SESSION["s"]["form"]["return_to_url"];
    	                $_SESSION["s"]["form"]["return_to_url"] = '';
        	            session_write_close();
            	        header("Location: ".$redirect);
                	    exit;
                	} else {
                    	header("Location: ".$app->tform->formDef['list_default']);
                	}
                	exit;
				}
			}

			unset($record);
		}

		parent::onSubmit();
	}
	
}

$page = new page_action;
$page->onLoad();

?>
