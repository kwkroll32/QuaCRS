<?php
class Report_model extends CI_Model {
	
	function __construct(){
		parent::__construct();
	}

	function generate_report($sampleIDs, $fields="*"){
		$where = "";
		if (is_array($sampleIDs)){
			if (!empty($sampleIDs)){
				foreach($sampleIDs as $id){
					$where .= "qcID = $id OR ";
				}
				$where = rtrim($where, "OR ");	
			}
		}
		else{
			$where = "qcID = $sampleIDs";
		}

		$this->db->select($fields);
		$this->db->where($where);
		$query= $this->db->get("qc");
		$result = $query->result_array();
		return $result;
	}

	function generate_csv_report($sampleIDs, $fields="*"){
		$this->load->dbutil();
		$where = "";
		if (is_array($sampleIDs)){
			if (!empty($sampleIDs)){
				foreach($sampleIDs as $id){
					$where .= "qcID = $id OR ";
				}
				$where = rtrim($where, "OR ");	
			}
		}
		else{
			$where = "qcID = $sampleIDs";
		}

		$this->db->select($fields);
		$this->db->where($where);
		$query= $this->db->get("qc");

		$data = $this->dbutil->csv_from_result($query);
		return $data;
	}
}
?>