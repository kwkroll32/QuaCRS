<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

  function __construct()
  {
    parent::__construct();
  }

  function index()
  {
    $this->load->helper('form');
	if($this->session->userdata('logged_in'))
    	{
    	  $session_data = $this->session->userdata('logged_in');
    	  $sdata['username'] = $session_data['username'];
	  $sdata['userID'] = $session_data['id'];
    	  redirect('sample', 'refresh');
    	  //echo $sdata['userID'];
    	}
    else
    	{
    	  //If no session, redirect to login page
    	  $this->load->view('pages/login_view');
		}    
  }

}

?>
