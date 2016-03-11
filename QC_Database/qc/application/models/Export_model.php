<?php
class Export_model extends CI_Model {

	function __construct(){
		parent::__construct();
	}

	function create_csv($header, $content, $id=null){
		$reportBasePath = config_item('report_path');
		$timeInSec = (string)time();
		if(strpos($id, ",") !== false)
			$reportFileName = 'Aggregate_'.$timeInSec.'_report.csv';
		else
			$reportFileName = (string)$id.'_'.$timeInSec.'_report.csv';

		$reportFullPath = $reportBasePath.$reportFileName;

		//if the report folder is not writable
		$permission = substr(sprintf('%o', fileperms($reportBasePath)), -4);
		if($permission !== "0777" && $permission !== "1777"){
			return "NOT ACCESSIBLE";
		}
		
		return $reportFileName;
	}
}
?>
