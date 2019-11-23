<?php

/*
Copyright (c) 2013, Marius Cramer, pixcept KG
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

class cronjob_monitor_openvz extends cronjob {

	// job schedule
	protected $_schedule = '*/5 * * * *';
	protected $_run_at_new = true;

	private $_tools = null;

	/* this function is optional if it contains no custom code */
	public function onPrepare() {
		global $app;

		parent::onPrepare();
	}

	/* this function is optional if it contains no custom code */
	public function onBeforeRun() {
		global $app;

		return parent::onBeforeRun();
	}

	public function onRunJob() {
		global $app, $conf;

		/* used for all monitor cronjobs */
		$app->load('monitor_tools');
		$this->_tools = new monitor_tools();
		/* end global section for monitor cronjobs */

		/* the id of the server as int */
		$server_id = intval($conf['server_id']);

		/** The type of the data */


		$type = 'openvz_veinfo';

		/*
			Fetch the data into a array
		 */
		$app->load('openvz_tools');
		$openVzTools = new openvz_tools();
		$data = $openVzTools->getOpenVzVeInfo();

		/* the VE-Info has no state. It is, what it is */
		$state = 'no_state';

		$res = array();
		$res['server_id'] = $server_id;
		$res['type'] = $type;
		$res['data'] = $data;
		$res['state'] = $state;

		/*
		 * Insert the data into the database
		 */
		$sql = 'REPLACE INTO monitor_data (server_id, type, created, data, state) ' .
			'VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?)';
		$app->dbmaster->query($sql, $res['server_id'], $res['type'], serialize($res['data']), $res['state']);

		/* The new data is written, now we can delete the old one */
		$this->_tools->delOldRecords($res['type'], $res['server_id']);

		/** The type of the data */
		$type = 'openvz_beancounter';

		/*
			Fetch the data into a array
		 */
		$app->load('openvz_tools');
		$openVzTools = new openvz_tools();
		$data = $openVzTools->getOpenVzVeBeanCounter();

		/* calculate the state of the beancounter */
		if ($data == '') {
			$state = 'no_state';
		} else {
			$state = 'ok';

			/* transfer this output-string into a array */
			$test = explode("\n", $data);

			/* the first list of the output is not needed */
			array_shift($test);

			/* now process all items of the rest */
			foreach ($test as $item) {
				/*
				 * eliminate all doubled spaces and spaces at the beginning and end
				 */
				while (strpos($item, '  ') !== false) {
					$item = str_replace('  ', ' ', $item);
				}
				$item = trim($item);

				/*
				 * The failcounter is the LAST
				 */
				if ($item != '') {
					$tmp = explode(' ', $item);
					$failCounter = $tmp[sizeof($tmp) - 1];
					if ($failCounter > 0)
						$state = 'info';
					if ($failCounter > 50)
						$state = 'warning';
					if ($failCounter > 200)
						$state = 'critical';
					if ($failCounter > 10000)
						$state = 'error';
				}
			}
		}

		$res = array();
		$res['server_id'] = $server_id;
		$res['type'] = $type;
		$res['data'] = $data;
		$res['state'] = $state;

		/*
		 * Insert the data into the database
		 */
		$sql = 'REPLACE INTO monitor_data (server_id, type, created, data, state) ' .
			'VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?)';
		$app->dbmaster->query($sql, $res['server_id'], $res['type'], serialize($res['data']), $res['state']);

		/* The new data is written, now we can delete the old one */
		$this->_tools->delOldRecords($res['type'], $res['server_id']);

		parent::onRunJob();
	}

	/* this function is optional if it contains no custom code */
	public function onAfterRun() {
		global $app;

		parent::onAfterRun();
	}

}

?>
