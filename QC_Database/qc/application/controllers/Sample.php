<?php
/*
 * @author Taha Mazher Topiwala
 */

defined('BASEPATH') OR exit('No direct script access allowed');
class Sample extends CI_Controller{
    public function __construct(){
        parent::__construct();

        $this->load->helper('utility');
        $this->load->model("Sample_model");
        $this->load->helper('form');
    }


	public function index(){

            if($this->session->userdata('logged_in'))
            {
              $session_data = $this->session->userdata('logged_in');
              $sdata['username'] = $session_data['username'];
              $sdata['userID'] = $session_data['id'];
            }
            else
            {
              //If no session, redirect to login page
              redirect('login', 'refresh');
            }

            $samples = $this->Sample_model->get_all_samples($sdata['userID'],"*",true);
            //$columns = $this->Sample_model->get_columns("qc");

            // since we are going to be using the columns from the views in the columns dropdown,
            // we need to have the columns in the same order as they appear in the columns dropdown for the table
            $columns = array();

            $viewNames = $this->Sample_model->get_table_names("sample_view");
            //$viewNames = array("general","genomic_stats","alignment_stats","fastqc_stats", "GC_content", "library_stats", "mapping_duplicates", "sequence_duplicates", "strand_stats");
            foreach ($viewNames as $viewName){
                    $data['view'][$viewName] = $this->Sample_model->get_columns($viewName);
                    foreach($data['view'][$viewName] as $column){
                            if($column['Field']=="qcID")
                                    continue;
                            $columns[] = $column;
                    }
            }

            $cumulativeColumnArray = array();

            foreach ($viewNames as $viewName) {
              $cumulativeColumnArray[$viewName] = $this->Sample_model->get_columnsNames($viewName);
            }

            // Get All Studies

            $study = $this->Sample_model->getOnlyStudy();

            $data['study'] = $study;

            $data['searchColumns'] = $cumulativeColumnArray;

            $head['title'] = "Sample List";
            $navbar['selected']="home";
            $data['samples'] = $samples;
            $data['columns'] = $columns;
            $data['searchColumns'] = $cumulativeColumnArray;
            $data['flags'] = get_percent_flags();
            $data['defaultColumns'] = get_column_order();//array("Unique_ID", "Sample", "Study");

            $this->load->view('templates/head', $head);
            $this->load->view('templates/header', $navbar);

            $this->load->view("sample_view", $data);
            $this->load->view('templates/footer');
	}

	public function detail($sampleID){

        if($this->session->userdata('logged_in'))
        {
          $session_data = $this->session->userdata('logged_in');
          $sdata['username'] = $session_data['username'];
          $sdata['userID'] = $session_data['id'];
          //echo $sdata['userID'];
          //echo $sdata['username'];
        }
        else
        {
          //If no session, redirect to login page
          redirect('login', 'refresh');
        }

        $data = array();
        if (!is_numeric($sampleID)){
                echo "ERROR: ".$sampleID;
                return 1;
        }
        if (! $this->Sample_model->is_allowed($session_data['id'], $sampleID) )
        {
           $head['title'] = "DNE";
           $navbar['selected']="home";
           $this->load->view('templates/head', $head);
           $this->load->view('templates/header', $navbar);


           $this->load->view('templates/footer');
           echo "this sample does not exist";
           return;
        }

        $sampleName= $this->Sample_model->get_sample_name($sampleID);
        $viewNames = $this->Sample_model->get_table_names("sample_detail_view");
        //$viewNames = array("genomic_stats","alignment_stats","fastqc_stats", "GC_content", "library_stats", "mapping_duplicates", "sequence_duplicates", "strand_stats");
        $i = 0;
        foreach ($viewNames as $viewName){
                $data['views'][$viewName] = $this->Sample_model->get_sample_view($viewName,$sampleID);
        }

        $data['qcID'] = $sampleID;
        $data['sample'] = $sampleName;
        $data['viewNames'] = $viewNames;
        $data['img'] = $this->Sample_model->get_images($sampleID);
        $data['flags'] = get_percent_flags();

        $head['title'] = "Sample Detail for $sampleName";
        $navbar['selected']="home";

        $this->load->view('templates/head', $head);
        $this->load->view('templates/header', $navbar);

        $this->load->view("sample_detail_view", $data);
        $this->load->view('templates/footer');
	}

    public function CompareView(){
        $data = array();

        if($this->session->userdata('logged_in'))
        {
            $session_data = $this->session->userdata('logged_in');
            $sdata['username'] = $session_data['username'];
            $sdata['userID'] = $session_data['id'];
        }else{
            //If no session, redirect to login page
            redirect('login', 'refresh');
        }

        $i = 0;

        $groupCount = $this->input->post('groupnumber', TRUE);

        if($groupCount == NULL){
            $base_url = $this->config->item('base_url');
            echo "<script type='text/javascript'>alert('No samples selected for compare report!'); window.location.replace('$base_url');</script>";
        }

        //Complete Number of Groups

        $data['groupcount'] = $groupCount;

        //Setting groups in array

        $groupArray = array();
        $groupArrayName = array();
        $groupArrayColor = array();
        $groupSampleName = array();
        $groupSampleId = array();
        $singleGroupArray = array();
        $idString = "";

        $nameEditableString = ltrim($this->input->post('groupnames', TRUE),",");

        $nameEditable = explode(",",$nameEditableString);

        $groupcolor = ltrim($this->input->post('groupcolor', TRUE),",");

        $groupcolor = explode(",",$groupcolor);

        // Setting up Variables for saving compareResults to be sent to view

        $groupnamesFormString = "";
        $groupcolorsFormString = "";
        $groupvaluesFormString = "";

        for($i = 0; $i < $groupCount; $i++){
            $groupName = 'group-'.$i;
            $groupArrayName[$i] = $nameEditable[$i];
            $groupArrayColor[$i] = $groupcolor[$i];
            $groupnamesFormString .= $nameEditable[$i].";";
            $groupcolorsFormString .= $groupcolor[$i].";";
            $groupValue = $this->input->post($groupName,True);
            $value = explode(",", $groupValue);
            for($j = 0; $j< count($value); $j++){
                $groupArray[$i][$j] = $value[$j];
                $groupSampleName[$i][$j] = $this->Sample_model->get_sample_name($value[$j]);
                $groupSampleId[$i][$j] = $value[$j];
                $idString .= $value[$j].",";

                $groupvaluesFormString .= $value[$j].",";
            }
            $groupvaluesFormString = rtrim($groupvaluesFormString, ",");
            $groupvaluesFormString = $groupvaluesFormString.";";
        }

        // Local Storage Array

        $SampleID = array();
        $SampleID = $groupArray;

        $data['MasterGroupWithID'] = $SampleID;

        $data['groupnamesFormString'] = rtrim($groupnamesFormString,";");
        $data['groupvaluesFormString'] = rtrim($groupvaluesFormString,";");
        $data['groupcolorsFormString'] = rtrim($groupcolorsFormString,";");

        $idString = rtrim($idString, ", ");
        $data['idString'] = $idString;

        $singleGroupArray = explode(",", $idString);
        $data['singleGroupArray'] = $singleGroupArray;

        // Get all table views

        $viewNames = $this->Sample_model->get_table_names("aggregate_detail_view");

        $data['viewNames'] = $viewNames;
        $viewNameAndColumnName = array();
        foreach($viewNames as $viewName){
            $viewNameAndColumnName[$viewName] = $this->Sample_model->get_columns($viewName);
        }

        //Fast QC

        $fastqc_aggregate_result = array();

        for($i = 0; $i < count($groupArray); $i++){
            $fastqc_aggregate_result[$i] = $this->Sample_model->get_fastqc_aggregate_view($groupArray[$i]);
        }

        $data['fastqc_aggregate_result'] = $fastqc_aggregate_result;

        for($i = 0; $i < count($groupArray); $i++){
            foreach($viewNames as $viewName){
                $data['aggregate_result_views'][$i][$viewName] = $this->Sample_model->get_aggregate_view($viewName, $groupArray[$i]);
                $data['plot_info'][$i][$viewName] = $this->Sample_model->get_samples_info($viewName, $groupArray[$i]);
                $data['per_row_info'][$i][$viewName] = $this->Sample_model->get_agg_plot_info($viewName, $groupArray[$i]);
            }
        }

        /*
            Preparing Initialisation Vanish Metric
        */

        foreach ($viewNames as $viewName) {
            if($viewName != "General"){
                $magicalVanishMetric[$viewName] = $this->Sample_model->initiateForMagicalVanishMetric($viewName,$singleGroupArray);
            }
        }

        $data['magicalVanishMetric'] = $magicalVanishMetric;

        /*
            Get Value of each column related to Qc ID for ANOVA Test
        */

        if($groupCount > 1){
            if(count($groupArray[1]) > 1){
                $data['ANOVAPresence'] = true;
                foreach ($viewNames as $viewName) {
                    $relatedValue[$viewName] = $this->Sample_model->getViewNameTableAndGroupCalculation($viewName,  $groupArray,  $singleGroupArray);
                }
                $data['ANOVA'] = $relatedValue;
            }else{
                $data['ANOVAPresence'] = false;
            }
        }else{
            $data['ANOVAPresence'] = false;
        }

        /*
            Preparations for Graphing Data
        */

        foreach ($viewNames as $viewName) {
            $graphValue[$viewName] = $this->Sample_model->getViewNameTableAndGroupGraphingValues($viewName,  $groupArray);
        }

        $data['singleGraphValue'] = $graphValue;

        for ($i=0; $i < count($singleGroupArray); $i++) {
            $singleGraphSampleNames[] = $this->Sample_model->getSampleNamesForGraphs($singleGroupArray[$i]);
        }
        $data['singleGraphSampleNames'] = $singleGraphSampleNames;

        /* Get All Coloumns In Single Array */

        foreach ($viewNames as $viewName) {
            $cumulativeColumns[$viewName] = $this->Sample_model->get_columnsNames($viewName);
        }

        $data['allColumns'] = $cumulativeColumns;

        /* Get All Columns */

        $data['flags'] = get_percent_flags();

        $data['groupArray'] = $groupArray;
        $data['groupArrayName'] = $groupArrayName;
        $data['groupArrayColor'] = $groupArrayColor;
        $data['sampleNames'] = $groupSampleName;
        $data['sampleIdArray'] = $groupSampleId;

        // Get Complete Set of Aggregate Result's

        $fastqc_aggregate_result_cumulative = $this->Sample_model->get_fastqc_aggregate_view($data['singleGroupArray']);

        $data['fastqc_aggregate_result_cumulative'] = $fastqc_aggregate_result_cumulative;

        foreach($viewNames as $viewName){
            $data['aggregate_result_views_cumulative'][$viewName] = $this->Sample_model->get_aggregate_view($viewName,$data['singleGroupArray']);
            $data['plot_info_cumulative'][$viewName] = $this->Sample_model->get_samples_info($viewName, $data['singleGroupArray']);
            $data['per_row_info_cumulative'][$viewName] = $this->Sample_model->get_agg_plot_info($viewName, $data['singleGroupArray']);
        }

        //Column names for viewnames
        $data['viewNameAndColumnName'] = $viewNameAndColumnName;

        $head['title'] = "Compared Results";
        $navbar['selected']= "home";

        $this->load->view('templates/head', $head);
        $this->load->view('templates/header', $navbar);

        $this->load->view("compare_detail_view", $data);
        $this->load->view('templates/footer');
    }

    public function singleView(){
        $base_url = $this->config->item('base_url');
        $resources = $this->config->item('resources');
        $precision = $this->config->item('precision');
        $sampleID = $this->input->post('sampleid', TRUE);
        $sampleName= $this->Sample_model->get_sample_name($sampleID);
        $sampleStudy = $this->Sample_model->get_sample_specific_table_info($sampleID,"General");
        $viewNames = $this->Sample_model->get_table_names("sample_detail_view");

        foreach ($viewNames as $viewName){
            $view[$viewName] = $this->Sample_model->get_sample_view($viewName, $sampleID);
        }

        function reformat_number($output, $precision){

            $this_exploded = explode('.',$output);
            $this_length = strlen($this_exploded[1]);

            if($this_length>=3){
                echo number_format($output);

            }elseif($this_length==2){
                echo number_format($output,1);

            }elseif($this_length==1){
                echo number_format($output,2);

            }else{
                echo number_format($output,$precision);
            }
        }

        $imageLinks = $this->Sample_model->getLinkForImages($sampleID);

        $study =  $sampleStudy[0]['Study'];
        $run_description =  $sampleStudy[0]['Run_Description'];

        echo <<<EOF
            <div class="menu menuSampleInfo" id="menu" overlay>
                <div class="topBanner"><p>Sample Information</p></div>
                <p><strong>Sample Name</strong> : $sampleName</p>
                <p><strong>Sample ID</strong> : $sampleID</p>
                <p><strong>Study</strong> : $study</p>
                <p><strong>Run Description</strong> : $run_description</p>
                <form action="{$base_url}index.php/ajax/generate_report" method="POST" accept-charset="utf-8">
                    <input class="hidden" name = "id" value = "$sampleID" />
                    <button class="btn btn-success" name="submit" title="Download Report">Download Sample Report</button>
                </form>
            </div>
EOF;

        echo <<<EOF
            <div class="menu" id="menu" overlay>
                <div class="topBanner"><p>Tables Included</p></div>
EOF;
                foreach($viewNames as $viewName){
                    echo '<div class="linkHold">';
                        echo '<p>'.ucfirst(str_replace("_"," ",$viewName)).'</p>';
                    echo "</div>";
                }
        echo "</div>";

        echo "<div class='tabBar' id='tabBar'>";
            echo <<<EOF
                <button class="buttonTab" id="left" data-toggle="1" onclick="switchViewsBackDrop(this)">
                    Table Data
                </button>
                <button class="buttonTab" id="right" data-toggle="2" onclick="switchViewsBackDrop(this)">
                    Plot Pictures
                </button>
EOF;
        echo "</div>";

        echo "<div class='toggleInfoHold' id='toggleInfoHold'>";
            echo"<div class='info' id='info'>";
                foreach ($viewNames as $viewName){
                  if($viewName != 'fastQC_Stats'){
                      echo "<div class='viewHold' id='$viewName'>";
                          echo "<div class='topBanner'>";
                               echo "<p style='float:left'>".ucfirst(str_replace("_"," ",$viewName))."</p>";
                          echo "</div>";
                          echo "<div class='tableData'>";
                              echo "<div class='table'>";
                                  echo "<div class='tableHeader border-bottom-light'>";
                                      echo <<<EOF
                                          <div class="tableColumn" metric-half><p>Metric</p></div>
                                          <div class="tableColumn" metric-half><p>Value</p></div>
EOF;
                                  echo "</div>";
                                  echo "<div class='tableColumnWrap' open>";
                                      foreach($view[$viewName] as $key => $value){
                                          if($key !== 'qcID'){
                                              echo "<div class='tableRow border-bottom-light'>";
                                                  echo "<div class='tableColumn border-right design-font' metric-half>";
                                                      echo "<p>".str_replace("_"," ",$key)."</p>";
                                                  echo "</div>";
                                                  echo "<div class='tableColumn' metric-half>";
                                                      echo "<p>".number_format($value, $precision)."</p>";
                                                  echo "</div>";
                                              echo "</div>";
                                          }
                                      }
                                  echo "</div>";
                              echo "</div>";
                          echo "</div>";
                      echo "</div>";
                    }
                }
            echo "</div>";
            // Plot Information Hold
            echo "<div class='info hideContent' id ='info'>";
                echo "<div class='imageWell'>";
                    echo "<div class='imagehead'><p>Sample Images</p></div>";
                    echo "<div class='imageContent'>";
                      for ($i = 0; $i < count($imageLinks); $i++){
                        echo "<img src = '".$resources."img/".$imageLinks[$i]."'>";
                      }
                    echo "</div>";
                echo "</div>";
            echo "<div>";
        echo"</div>";
    }
}
?>
