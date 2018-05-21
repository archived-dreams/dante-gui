<?php
namespace App;

use SSH;

class ExecProvider {

	public static function run($commands, $function = false) {
		if (env('USE_SSH')) {
			return SSH::run($commands, $function);
		}

		$satuses = [];
		foreach ($commands as $i => $command) {
			$command = 'sudo ' . $command;
			$statuses[$i] = exec($command, $output);
			foreach ($output as $line) {
				if ($function) {
					call_user_func($function, $line);
				}
			}
		}
		return $statuses;
	}

}