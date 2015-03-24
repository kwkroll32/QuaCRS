<?php
class Pages extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}

	public function view($page="aboutus"){
		if ( ! file_exists('application/views/pages/'.$page.'.php')){
			// Whoops, we don't have a page for that!
			show_404();
		}

		/*if($this->session->userdata('logged_in'))
    		{
    		  $session_data = $this->session->userdata('logged_in');
    		  $sdata['username'] = $session_data['username'];
    		  //echo $sdata['username'];
    		}
    		else
    		{
    		  //If no session, redirect to login page
    		  redirect('login', 'refresh');
		}*/

		$head['title'] = ucwords($page);
		$navbar['selected']=$page;
		$this->load->view('templates/head', $head);
		$this->load->view('templates/header', $navbar);
		$this->load->view('pages/'.$page);
		$this->load->view('templates/footer');
	}
}
