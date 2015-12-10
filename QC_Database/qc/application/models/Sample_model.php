<?php
class pValueStatistics extends CI_Model{
	function __construct(){
		parent::__construct();
	}

	public function getDegreeOfFreedom($totalSampleCount, $groupArray){
		$groupCount = count($groupArray);
		$nd1 = $groupCount - 1;
		$nd2 = $totalSampleCount - $groupCount;
		return array($nd1, $nd2);
	}

	public function Fstatistic($x, $nd1, $nd2)
	{
	  $pvalue = 1.0;

	  if ($x!=0.0){

	    $u=$x;

	    if ($nd1 + $nd2 > 69 || ($nd1 > 10 && $nd2 > 15)) {
	      /* use approximation for large degrees of freedom */
			$a1=2.0/(9.0*$nd1);
			$a2=2.0/(9.0*$nd2);
			$u=((1.0-$a2)*pow($x,0.333333)-1.0+$a1)/sqrt(pow($x,0.666667)*$a2+$a1);
			$t=1.0/(1.0+.2316419*abs($u));
			$pvalue=$t*(0.31938153-$t*(0.35656378-$t*(1.7814779-$t*(1.82125598-1.33027443*$t))))*0.3989423*exp(-$u*$u/2.0);
	    } else {
	      /* degrees of freedom are within reasonable range */
	      	$nn=$nd1;
			$f=$nd2/($nd2+$nd1*$x);
			$t=1.0-2.0*$f;
			$q=array(
			   1 => 0.5+atan($t/sqrt(1.0-$t*$t))/3.1415927,
			   2 => 1.0-sqrt($f)
			   );
			$gb=sqrt(3.1415927);
			$gab=array(
			 1 => 1.0,
			 2 => $gb
			 );
			$b=0.5;
			$y=1.0-$f;
	      
		    for($jj=1; $jj<=2; $jj++) {
				$ga = array(
					  1 => sqrt(3.1415927),
					  2 => 1.0
					);

				if ($nn>2) {
				  	for($i=3;$i<=$nn;$i++) {
				    	$j=(($i+1) % 2)+1;
				    	$a=($i-2.0)*0.5;
				    	$ga[$j]=$a*$ga[$j];
				    	$gab[$j]=($a+$b-1.)*$gab[$j];
				    	if ($gab[$j]<=0.0) {
				      		$gab[$j]=1.0;
				    	}
				    	$q[$j]=$q[$j]-$gab[$j]/($ga[$j]*$gb)*pow($y,$a)*pow(1.0-$y,$b);
				  	}
				  	if ($jj==2) break;
				} else {
				  	$j=$nn;
				}

				$b=$nn*0.5;
				
				$q=array(
					 1 => 1.0-$q[$j],
					 2 => 1.0-pow($y,$b)
					 );
				
				$nn=$nd2;
				
				if ($nn<=2) {
				  $j=$nn;
				  break;
				}
				
				$gb=$ga[$j];
				$gab=array(
					   1 => $gab[$j],
					   2 => $gb
					   );
				$y=$f;	   
		    }
	      	$pvalue=$q[$j];
	    }

	    /* invert p-value if test statistic is negative */
	    if ($u < 0.0) {
	      	$pvalue = 1.0 - $pvalue;
	    }
	    /* make sure we do not report p-values larger than 1 */
	    if ($pvalue > 1.0) {
	      	$pvalue = 1.0;
	    }
	  }
	  return $pvalue;
	}
}
class Sample_model extends pValueStatistics {

	function __construct(){
        parent::__construct();
    }

	function get_all_samples($userID, $fields="*", $aggregate=false){
		$command = "SELECT $fields FROM qc JOIN permissions ON qc.Study=permissions.project where permissions.userID=$userID";
		if ($aggregate)
			$command.=" AND Shown=1";
		$query = $this->db->query($command);

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
	    $notTableList[4] = "fastQC_Stats";
        }

        if ($viewPage == "aggregate_detail_view"){
            $notTableList[3] = "General";
            $notTableList[4] = "fastQC_Stats";
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

	function get_columns($viewName){
		$query = $this->db->query("SHOW COLUMNS FROM $viewName");
		return $query->result_array();
	}

	function get_columnsNames($viewName){
		$result = array();
		$column = $this->get_columns($viewName);
		foreach ($column as $value) {
			if($value['Field'] !== "qcID"){
				$result[] = $value['Field'];
			}
		}	
		return $result;
	}

	public function get_sample_specific_table_info($sampleid, $tablename)
	{
		$result = array();
		$sql = "SELECT * FROM `$tablename` WHERE qcID = '$sampleid'";
		$query = $this->db->query($sql);
		$result = $query->result_array();
		return $result;
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
		$viewName = "fastQC_Stats";
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

	/*
		Initiate  For Magical Vanish Metric
	*/

	function initiateForMagicalVanishMetric($viewName, $singleGroupArray){
		$result = array();
		$columns = $this->get_columns($viewName);
		foreach($columns as $column) {
			if($column['Field'] != "qcID"){
				$result[$column['Field']] = $this->getMagicalVanishMetric($viewName,$column['Field'],$singleGroupArray);
			}
		}
		return $result;
	}

	function getMagicalVanishMetric($viewName,$column,$singleGroupArray){
		$idString = "";
		for($i = 0; $i< count($singleGroupArray); $i++){
			$idString = $idString.",".$singleGroupArray[$i];
		}
		$select = 'MIN(`'.$column.'`) as min , MAX(`'.$column.'`) as max , AVG(`'.$column.'`) as avg';
		$where = $idString = ltrim($idString, ",");
		$sql = "SELECT ".$select." FROM ".$viewName." WHERE qcID in($where)";
		$query = $this->db->query($sql);
		$row = $query->result_array();
		$result = $this->decideMagicalVanishMetric($row[0]);
		
		return $result;
	
	}

	function decideMagicalVanishMetric($data){
		$avg = $data['avg'];
		$min = $data['min'];
		$max = $data['max'];
		if(($min == null || $min == 0) && ($avg == null || $avg == 0) && ($max == null || $max == 0)){
			return true;
		}else{
			return false;
		}
	}

	/*
		Get each value for sample
	*/

	function getViewNameTableAndGroupGraphingValues($viewName, $groupArray){
		$result = array();
		$columns = $this->get_columns($viewName);
		for($i = 0; $i < count($groupArray); $i++){
			foreach ($columns as $column) {
				if($column['Field']  != "qcID"){
					$result[$column['Field']][] = $this->getSampleValue($viewName,$column,$groupArray[$i]);
				}
			}
		}
		return $result;
	}

	function getSampleValue($viewName,$column,$singleGroupArray){
		$result = array();
		for ($i=0; $i < count($singleGroupArray); $i++) {
			$qcId = $singleGroupArray[$i];
			$sql = "SELECT * FROM $viewName WHERE qcID = '$qcId'";
			$query = $this->db->query($sql);
			$row = $query->result_array();
			for ($j=0; $j < count($row); $j++) { 
				$result[] = $row[$j][$column['Field']];
			}
		}
		return $result;
	}

	function getSampleNamesForGraphs($qcId){
		$sql = "SELECT Sample FROM qc WHERE qcID = '$qcId'";
		$query = $this->db->query($sql);
		$row = $query->result_array();
		$x = $row[0];
		return $x['Sample'];
	}

	/*  
		Calculation function performing 
		ANOVA INITIALISATION VALUES
		Please review the folling PDF for more
		information on ANOVA calculations
		http://cba.ualr.edu/smartstat/topics/anova/example.pdf
	*/

	function getViewNameTableAndGroupCalculation($viewName,  $groupArray,  $singleGroupArray){
		$result = array();
		$columns = $this->get_columns($viewName);
		$totalSampleCount = $this->getTotalSampleCount($singleGroupArray);
		foreach ($columns as $column) {
			if($column['Field'] !== "qcID"){
				$groupValueArray = $this->getSampleValuesForGroups($groupArray, $viewName, $column['Field']);
				$grandMean = $this->calculateGrandMean($singleGroupArray, $column['Field'], $viewName);
				$simpleMean = $this->calculateSimpleMean($groupValueArray);
				$sst = $this->calculateSST($grandMean,$groupValueArray);
				$sstr = $this->calculateSSTR($grandMean,$simpleMean,$groupArray);
				$sse = $this->calculateSSE($sstr, $sst);
				$mst = $this->calculateMST($sst, $groupArray);
				$mstr = $this->calculateMSTR($sstr, $groupArray);
				$mse = $this->calculateMSE($sse, $groupArray);
				$f = $this->calculateF($mstr, $mse);
				list($nd1,$nd2) = $this->getDegreeOfFreedom($totalSampleCount, $groupArray);
				$p = $this->Fstatistic($f, $nd1, $nd2);
				$result[$column['Field']] = number_format($p, 2);
			}
		}
		return $result;
	}

	/*  
		Calculation function performing 
		TOTAL NUMBEOF SAMPLE 
	*/

	function getTotalSampleCount($singleGroupArray){
		$count = count($singleGroupArray);
		return $count;
	}

	/*  
		Calculation function performing 
		GRAND MEAN
	*/

	function calculateGrandMean($singleGroupArray, $column, $viewName){
		$value = 0;
		$idString = "";
		for($i = 0; $i< count($singleGroupArray); $i++){
			$idString = $idString.",".$singleGroupArray[$i];
		}
		$idString = ltrim($idString, ",");
		$sql = "SELECT * FROM $viewName WHERE qcID in($idString)";
		$query = $this->db->query($sql);
		$row = $query->result_array();
		for($i = 0; $i < count($row); $i++){
			$value = $value + $row[$i][$column];
		}
		$value = $value / count($singleGroupArray);
		return $value;
	}

	/*  
		Calculation function performing 
		SIMPLE MEAN
	*/

	function calculateSimpleMean($groupValueArray){
		$mean = array();
		for($i = 0; $i < count($groupValueArray); $i++){
			$value = 0;
			for($j = 0; $j < count($groupValueArray[$i]); $j++){
				$value = $value + $groupValueArray[$i][$j];
			}
			$mean[] = $value / count($groupValueArray[$i]);
		}
		return $mean;
	}

	function performSquareCaluculation($grandMean, $metricValue){
		$subtractValue = $metricValue - $grandMean;
		$square = $subtractValue * $subtractValue;
		return $square;
	}

	/*  
		Calculation function performing 
		TOTAL SUM OF SQUARES
	*/

	function calculateSST($grandMean,$groupValueArray){
		$sst = 0;
		for($i = 0; $i < count($groupValueArray); $i++){
			for($j = 0; $j < count($groupValueArray[$i]); $j++){
				$value = $groupValueArray[$i][$j];
				$sst = $sst + $this->performSquareCaluculation($grandMean, $value);
			}
		}
		return $sst;
	}

	/*  .
		Calculation function performing 
		TREATMENT SUM OF SQUARES
	*/

	function calculateSSTR($grandMean, $simpleMean, $groupArray){
		$sstr = 0;
		$numberOfObservations = array();

		for($i = 0; $i < count($groupArray); $i++){
			$numberOfObservations[] = count($groupArray[$i]);
		}

		for($i = 0; $i < count($simpleMean); $i++){
			$sstr = $sstr + ($numberOfObservations[$i] * $this->performSquareCaluculation($grandMean,$simpleMean[$i]));
		}
		return $sstr;
	}

	/*  
		Calculation function performing 
		ERROR SUM OF SQUARES
	*/

	function calculateSSE($sstr, $sst){
		return $sst - $sstr;
	}

	/*  
		Calculation function performing 
		TOTAL MEAN OF SQUARES
	*/

	function calculateMST($sst, $groupArray){
		$numberOfObservations = 0;
		for($i = 0; $i < count($groupArray); $i++){
			$numberOfObservations = $numberOfObservations + count($groupArray[$i]);
		}
		$mst = ($sst)/($numberOfObservations-1);

		return $mst;
	}

	/*  
		Calculation function performing 
		MEAN SQAURE TEATMENT
	*/

	function calculateMSTR($sstr, $groupArray){
		$mstr = 0;
		$numberOfColumns = 0;
		$max = 0;
		$min = 10;
		for($i = 0; $i < count($groupArray); $i++){
			$numberOfColumns = count($groupArray[$i]);
			if($numberOfColumns < $min){
				$min = $numberOfColumns;
			}else if($numberOfColumns > $max){
				$max = $numberOfColumns;
			}
		}
		$mstr = ($sstr) / ($max-1);
		return $mstr;
	}

	/* 
		Calculation function performing 
	   	TOTAL SQUARE ERROR
	*/

	function calculateMSE($sse, $groupArray){
		$mse = 0;
		$numberOfObservations = 0;

		for($i = 0; $i < count($groupArray); $i++){
			$numberOfObservations = $numberOfObservations + count($groupArray[$i]);
		}

		$numberOfColumns = 0;
		$max = 0;
		$min = 10;
		for($i = 0; $i < count($groupArray); $i++){
			$numberOfColumns = count($groupArray[$i]);
			if($numberOfColumns < $min){
				$min = $numberOfColumns;
			}else if($numberOfColumns > $max){
				$max = $numberOfColumns;
			}
		}

		$mse = $sse / ($numberOfObservations - $max);

		return $mse;
	}

	/* 
		Calculation function performing F
	*/

	function calculateF($mstr, $mse){
		if($mse != 0 || $mstr != 0){
			return ($mstr / $mse);
		}else{
			return 0;
		}
	}

	function getSampleValuesForGroups($groupArray, $viewName, $column){
		$groupValueArray = array();
		for($i = 0; $i < count($groupArray); $i++){
			for($j = 0; $j < count($groupArray[$i]); $j++){
				$qcId = $groupArray[$i][$j];
				$sql = "SELECT * FROM $viewName WHERE qcID = '$qcId'";
				$query = $this->db->query($sql);
				$row = $query->result_array();
				for($k = 0; $k < count($row); $k++){
					$value = $row[$k][$column];
					$groupValueArray[$i][] = $value;
				}
			}
		}
		return $groupValueArray;
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

	function get_agg_plot_info($viewName, $samplesArr){
		$columns = $this->get_columns($viewName);
		$result = array();
		$select = "unique_ID, ";

		foreach($columns as $column){
			if($column['Field'] == "qcID")
				continue;
			$select .= $column['Field'].", ";
		}

		$select = rtrim($select, ", ");
		$where = "";
		foreach($samplesArr as $sampleID){
			$where .= "qcID = ".$sampleID." OR ";
		}
		$where = rtrim($where, "OR ");

		$this->db->select($select);
		$this->db->where($where);

		$query = $this->db->get('qc');
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

		$select = rtrim($select, ", ");
		$where = "";
		foreach($samplesArr as $sampleID){
			$where .= "qcID = ".$sampleID." OR ";
		}
		$where = rtrim($where, "OR ");

		$this->db->select($select);
		$this->db->where($where);

		$query = $this->db->get($viewName);
		$result = $query->result_array();

		return $result;
	}

	function produceSearchResultsJSON($keyword, $viewNames){
		$results = array();
		$finalResultingQCIDArray = array();
		$queryString = "";
		foreach ($viewNames as $viewname) {
			if($viewname == 'alignment_Stats' || $viewname == 'General'){
				$columns = $this->get_columns($viewname);
				foreach ($columns as $column) {
					if($column['Field'] != "qcID"){
						$field = preg_replace('/\s+/', '', $column['Field']);
						if($queryString == ""){
							$queryString = "`" . $field . "`" . " LIKE '%".$keyword."%' OR ";
						}else{
							$queryString = $queryString . "`" . $field . "`"  . " LIKE '%".$keyword."%' OR ";
						}
					}
				}
				$queryString = rtrim($queryString," OR ");
				$results = $this->unWrapQuery($viewname, $queryString);
				for($i = 0; $i < count($results); $i++){
					if(!in_array($results[$i], $finalResultingQCIDArray)){
						$finalResultingQCIDArray[] = $results[$i];
					}
				}
				$queryString = "";
			}
		}
		return json_encode($finalResultingQCIDArray);
	}

	function unWrapQuery($viewName, $queryString){
		$resultingQCIDArray = array();
		$this->db->where($queryString);
        $query = $this->db->get($viewName);
        $result = $query->result_array();
		for($i = 0; $i < count($result); $i++){
			$resultingQCIDArray[] = $result[$i]["qcID"];
		}
		return $resultingQCIDArray;
	}
}
?>
