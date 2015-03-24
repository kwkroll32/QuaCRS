<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller{

	public function __construct(){
		parent::__construct();
	}

	public function generate_report(){
		if(!isset($_POST['submit']) || !isset($_POST['id']))
			return;

		$this->load->model("Report_model", "MReport");
		$this->load->helper('download');

		$id = $_POST['id'];
		$idArr = explode(",", $id);
		$report = $this->MReport->generate_csv_report($idArr);
		
		if(count($idArr) > 1){
			$reportFileName = "Aggregate_report.csv";
		}
		else{
			$reportFileName = "{$id}_report.csv";
		}
		force_download($reportFileName, $report);
	}
}
?>