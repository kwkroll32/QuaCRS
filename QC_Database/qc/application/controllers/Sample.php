<?
defined('BASEPATH') OR exit('No direct script access allowed');

class Sample extends CI_Controller{
	public function __construct(){
		parent::__construct();

		$this->load->helper('utility');		
    	$this->load->model("Sample_model");
    	$this->load->helper('form');

    	//$this->output->enable_profiler(TRUE);
    }


	public function index(){

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
		
		$samples = $this->Sample_model->get_all_samples($sdata['userID'],"*",true);
		//$columns = $this->Sample_model->get_columns("qc");
		
		// since we are going to be using the columns from the views in the columns dropdown,
		// we need to have the columns in the same order as they appear in the columns dropdown for the table
		$columns = array();

		$viewNames = array("general","genomic_stats","alignment_stats","fastqc_stats", "GC_content", "library_stats", "mapping_duplicates", "sequence_duplicates", "strand_stats");
		foreach ($viewNames as $viewName){
			$data['view'][$viewName] = $this->Sample_model->get_columns($viewName);
			foreach($data['view'][$viewName] as $column){
				if($column['Field']=="qcID")
					continue;
				$columns[] = $column;
			}
		}

		$head['title'] = "Sample List";
		$navbar['selected']="home";
		$data['samples'] = $samples;
		$data['columns'] = $columns;
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
			echo "ERROR: $sampleID";
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
		$viewNames = array("genomic_stats","alignment_stats","fastqc_stats", "GC_content", "library_stats", "mapping_duplicates", "sequence_duplicates", "strand_stats");

		foreach ($viewNames as $viewName){
			$data['views'][$viewName] = $this->Sample_model->get_sample_view($viewName,$sampleID);
		}

		$data['qcID'] = $sampleID;
		$data['sample'] = $sampleName;
		$data['img'] = $this->Sample_model->get_images($sampleID);
		$data['flags'] = get_percent_flags();

		$head['title'] = "Sample Detail for $sampleName";
		$navbar['selected']="home";

		$this->load->view('templates/head', $head);
		$this->load->view('templates/header', $navbar); 
		
		$this->load->view("sample_detail_view", $data);
		$this->load->view('templates/footer');
	}

	public function aggregate(){
		$data = array();
       	foreach($this->input->post() as $key=>$value){
       		if (strpos($key, "sample-") !== false ){
       			$data['samples'][]= $value;
       		}
       	}
	
	if (array_key_exists('samples', $data))
	{
		$viewNames = array("genomic_stats","alignment_stats", "GC_content", "library_stats", "mapping_duplicates", "sequence_duplicates", "strand_stats");

		foreach($viewNames as $viewName){
			$data['aggregate_result_views'][$viewName] = $this->Sample_model->get_aggregate_view($viewName, $data['samples']);
			$data['plot_info'][$viewName] = $this->Sample_model->get_samples_info($viewName, $data['samples']);
		}

		$data['fastqc_aggregate_result'] = $this->Sample_model->get_fastqc_aggregate_view($data['samples']);
		$data['flags'] = get_percent_flags();
	
		$head['title'] = "Aggregate Details";
		$navbar['selected']="home";
		
		$this->load->view('templates/head', $head);
		$this->load->view('templates/header', $navbar);
		
		$this->load->view("aggregate_detail_view", $data);
		$this->load->view('templates/footer');
	}
	
	else
	{
		$base_url = $this->config->item('base_url');
		echo "<script type='text/javascript'>alert('No samples selected for aggregate report!'); window.location.replace('$base_url');</script>";
		#header("Location: $base_url");
       	}
	
	}
}
?>
