<?
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller{
	public function __construct(){
		parent::__construct();
    	$this->load->model("Sample_model");

    	$this->load->helper('url');
    	$this->load->helper('text');

    	$this->load->helper('utility');	

    	//$this->output->enable_profiler(TRUE);
    }

    public function index($keyword){
    		if($this->session->userdata('logged_in'))
    			{
    			  $session_data = $this->session->userdata('logged_in');
    			  $sdata['username'] = $session_data['username'];
    			  //echo $sdata['username'];
    			}
    		else
    			{
    			  //If no session, redirect to login page
    			  redirect('login', 'refresh');
				}
    	
    	$this->load->helper('form');

		// since we are going to be using the columns from the views in the columns dropdown,
		// we need to have the columns in the same order as they appear in the columns dropdown for the table
		$columns = array();
		$data = array();
		$columnNames = array();

		
		$viewNames = array("general","genomic_stats","alignment_stats","fastqc_stats", "GC_content", "library_stats", "mapping_duplicates", "sequence_duplicates", "strand_stats");
		foreach ($viewNames as $viewName){
			$data['view'][$viewName] = $this->Sample_model->get_columns($viewName);
			foreach($data['view'][$viewName] as $column){
				if($column['Field']=="qcID")
					continue;
				$columns[] = $column;
			}
		}
		
		$data['defaultColumns'] = get_column_order();
		foreach($data['defaultColumns'] as $ind){
			$columnNames[$columns[$ind]['Field']] = $columns[$ind]['Field'] ;
		}

		#Check for column specification
		$keyword_exploded = explode(":", $keyword);
		if(sizeof($keyword_exploded) >= 2){
			$newKeyword = trim($keyword_exploded[1]);
			$searchColumn = str_replace(" ", "_", trim($keyword_exploded[0]));
			$samples = $this->Sample_model->search_column($columnNames, $newKeyword, $searchColumn);
		}
		else{
			$samples = $this->Sample_model->search_samples($columnNames, $keyword);
		}

		$head['title'] = "Searching for $keyword";
		$navbar['selected']="home";
		$data['keyword'] = $keyword;
		$data['samples'] = $samples;
		$data['columns'] = $columns;
		$data['flags'] = get_percent_flags();
		


		$this->load->view('templates/head', $head);
		$this->load->view('templates/header', $navbar); 
		
		$this->load->view("sample_view", $data);
		$this->load->view('templates/footer');
    }


    public function presearch(){
    	if(!isset($_POST["keyword"]) || $_POST['keyword'] == ""){
    		redirect("/sample");
    	}
    	$keyword = $_POST["keyword"];
    	redirect("/search/index/$keyword");
    }

    public function advance(){

    }

}
?>
