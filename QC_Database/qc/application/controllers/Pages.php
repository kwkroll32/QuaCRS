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

		$head['title'] = ucwords($page);
		$navbar['selected']=$page;
		$this->load->view('templates/head', $head);
		$this->load->view('templates/header', $navbar);
		$this->load->view('pages/'.$page);
		$this->load->view('templates/footer');
	}
}
