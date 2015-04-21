<?php
class Sample_model extends CI_Model {
	function __construct(){
        parent::__construct();
    }
    
	function get_all_samples($userID, $fields="*", $aggregate=false){
		$command = "SELECT $fields FROM qc JOIN permissions ON qc.Study=permissions.project where permissions.userID=$userID";
		if ($aggregate)
			$command.=" AND Shown=1";
		$query = $this->db->query($command);

		/*$this->db->select($fields);
		
		if ($aggregate)
			$this->db->where("Shown", 1);

		$query = $this->db->get("qc");*/
		return $query->result_array();
	}

	function get_sample_detail($sampleID, $fields="*"){
		$this->db->select($fields);
		$this->db->where("qcID", $sampleID);
		$query = $this->db->get("qc");

		return $query->result_array();
	}

	function get_sample_view($viewName, $sampleID, $fields="*"){
		$this->db->select($fields);	      //select database
		$this->db->where("qcID", $sampleID);  //compare $sampleID to qcID
		$query = $this->db->get($viewName);   //runs select and where calls and builds the query for table ($viewname)
		$result = $query->result_array();     //query result as associative array
		return $result[0];
	}

	function get_sample_name($sampleID){
		$this->db->select("Sample");
		$this->db->where("qcID", $sampleID);
		$query= $this->db->get("qc");
		$result = $query->result_array();
		return $result[0]['Sample'];
	}

	function is_allowed($userID, $sampleID){
		$query = $this->db->query("SELECT COUNT(*) AS C FROM qc JOIN permissions ON qc.Study=permissions.project where permissions.userID=$userID AND qc.qcID=$sampleID");
		$result = $query->result_array();
		return $result[0]['C'];
	}

	function get_table_names($viewPage){
		$notTableList = array("permissions", "qc", "users");
		if ($viewPage == "sample_detail_view"){
			$notTableList[3] = "General";
		}
		if ($viewPage == "aggregate_detail_view"){
			$notTableList[3] = "General";
			$notTableList[4] = "fastqc_stats";
		}
		$query = $this->db->query("SHOW TABLES");
		$result = $query->result_array();
		$tableOut = array();
		$query = $this->db->query("SELECT DATABASE() as name;");
		$tmp = $query->row();
		$db_name = substr($tmp->name,0);
		
		$j = 0;
		for($i = 0; $i < count($result); $i++){
			if(!in_array($result[$i]["Tables_in_{$db_name}"], $notTableList)){
				$tableOut[$j]=$result[$i]["Tables_in_{$db_name}"];
				$j++;
			}
		}
		return $tableOut;
	}

	/*function get_hidden_names(){
		$query = $this->db->query("SHOW TABLES");
		$result = $query->result_array();
		$tableOut = array();
		$notHiddenList = array("general", "mapping_duplicates", "permissions", "qc", "sequence_duplicates", "users");
		$j = 0; */

	function get_columns($viewName){
		$query = $this->db->query("SHOW COLUMNS FROM $viewName");
		return $query->result_array();
	}

	function get_aggregate_view($viewName, $samplesArr){
		$columns = $this->get_columns($viewName);
		$select = "";	

		foreach($columns as $column){
			if($column['Field'] == "qcID")
				continue;

			$select .= "MIN(`".$column['Field']."`) as `min_".$column['Field']."` , AVG(`".$column['Field']."`) as `avg_".$column['Field']."` ,  MAX(`".$column['Field']."`) as `max_".$column['Field']."` , ";
		}
		$select = rtrim($select, ", "); #substr($select, 0, -2);

		$where = "";
		foreach($samplesArr as $sampleID){
			$where .= "qcID = ".$sampleID." OR ";
		}
		$where = rtrim($where, "OR "); #substr($where, 0, -3);

		$this->db->select($select);
		$this->db->where($where);
		$query = $this->db->get($viewName);
		$result = $query->result_array();
		return $result[0];
	}

	function get_fastqc_aggregate_view($samplesArr){
		$viewName = "fastqc_stats";
		$columns = $this->get_columns($viewName);
		$result = array();

		$where = "( ";
		foreach($samplesArr as $sampleID){
			$where .= "qcID = ".$sampleID." OR ";
		}
		$where = rtrim($where, "OR "); #substr($where, 0, -3);
		$where .= " ) ";
		

		foreach ($columns as $column){
			if($column['Field'] == "qcID")
				continue;

			$select = "COUNT(".$column['Field'].") as count_".$column['Field'];
			

			$this->db->select($select);
			$this->db->where($where." AND `".$column['Field']."` = 'pass'");
			$query = $this->db->get($viewName);

			$temp = $query->result_array();
			$result[$column['Field']]['pass'] = $temp[0]["count_".$column['Field']];

			$this->db->select($select);
			$this->db->where($where." AND `".$column['Field']."` = 'warn'");
			$query = $this->db->get($viewName);

			$temp = $query->result_array();
			$result[$column['Field']]['warn'] = $temp[0]["count_".$column['Field']];

			$this->db->select($select);
			$this->db->where($where." AND `".$column['Field']."` = 'fail'");
			$query = $this->db->get($viewName);

			$temp = $query->result_array();
			$result[$column['Field']]['fail'] = $temp[0]["count_".$column['Field']];
		}
		return $result;
	}

	function get_images($sampleID){
		$columns = $this->get_columns("qc");
		$select = "";
		foreach($columns as $key=>$val){
			if (strpos($val['Field'], "Location") !== False){
				$select .= "`".$val['Field']. "`, ";
			}
		}
		$select = rtrim($select, ", "); #$select = substr($select, 0, -2);

		$this->db->select($select);
		$this->db->where("qcID", $sampleID);
		$query = $this->db->get("qc");
		$result = $query->result_array();

		return $result[0];
	}



	function search_samples($columnNames, $keyword){
		$allColumns = $this->get_columns("qc");
		$selectedColumns = array();
		$isNumber = is_numeric($keyword);

		foreach($allColumns as $column){
			if(isset($columnNames[$column['Field']])){
				if(!$isNumber && (strpos($column['Type'], "int") === false && strpos($column['Type'], "decimal") === false  && strpos($column['Type'], "float") === false)){
					$selectedColumns[$column['Field']] = array($column['Field'], $column['Type']);
				}
				elseif($isNumber){
					$selectedColumns[$column['Field']] = array($column['Field'], $column['Type']);
				}
			}
		}
		$where = "";
		foreach($selectedColumns as $column){
			# The type the column is a number
			if(strpos($column[1], "int") !== false || strpos($column[1], "decimal") !== false  || strpos($column[1], "float") !== false ){
				$where .= "`".$column[0]."`" . " = " . $keyword. " OR ";
			}
				

			# The type the column is not a number
			else{
				$where .= "`".$column[0]."`" . " LIKE '%" . $keyword. "%' OR ";
			}
		}
		$where = rtrim($where, "OR ");
		#echo $where;
		$this->db->where($where);
		$query = $this->db->get("qc");
		$result = $query->result_array();

		return $result;
	}

	function get_samples_info($viewName, $samplesArr){
		$columns = $this->get_columns($viewName);
		$result = array();
		$select = "";
		
		foreach($columns as $column){
			if($column['Field'] == "qcID")
				continue;
			$select .= $column['Field'].", ";
		}
		
		$select = rtrim($select, ", "); #substr($select, 0, -2);
		$where = "";
		foreach($samplesArr as $sampleID){
			$where .= "qcID = ".$sampleID." OR ";
		}
		$where = rtrim($where, "OR "); #substr($where, 0, -3);

		$this->db->select($select);
		$this->db->where($where);
		$query = $this->db->get($viewName);
		$result = $query->result_array();
		return $result;
	}

	function search_column($columnNames, $keyword, $searchColumn){
		$allColumns = $this->get_columns("qc");
		$selectedColumns = array();
		$isNumber = is_numeric($keyword);

		foreach($allColumns as $column){
			if(isset($columnNames[$column['Field']])){
				if(strtolower($columnNames[$column['Field']]) == strtolower($searchColumn)){
					if(!$isNumber && (strpos($column['Type'], "int") === false && strpos($column['Type'], "decimal") === false  && strpos($column['Type'], "float") === false)){
						$selectedColumns[$column['Field']] = array($column['Field'], $column['Type']);
					}
					elseif($isNumber){
						$selectedColumns[$column['Field']] = array($column['Field'], $column['Type']);
					}
				}
			}
		}
		$where = "";

		if(sizeof($selectedColumns) == 0){
			$base_url = $this->config->item('base_url');
			echo "<script type='text/javascript'>alert('Invalid column name for search!'); window.location.replace('$base_url');</script>";
			return null;
		}
		else{
			foreach($selectedColumns as $column){
				# The type the column is a number
				if(strpos($column[1], "int") !== false || strpos($column[1], "decimal") !== false  || strpos($column[1], "float") !== false ){
					$where .= "`".$column[0]."`" . " = " . $keyword. " OR ";
				}
				
				# The type the column is not a number
				else{
					$where .= "`".$column[0]."`" . " LIKE '" . $keyword. "' OR ";
					#$where .= "`".$column[0]."`" . " LIKE '%" . $keyword. "%' OR ";
				}
			}
			$where = rtrim($where, "OR ");
			#echo $where;
			$this->db->where($where);
			$query = $this->db->get("qc");
			$result = $query->result_array();

			return $result;
		}
	}

}
?>
