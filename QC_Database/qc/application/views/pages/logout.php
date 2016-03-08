<?php
$base_url = $this->config->item('base_url');
$precision = $this->config->item('precision');
$resources = $this->config->item('resources');

$this->session->unset_userdata('logged_in');
#session_destroy();
//redirect('home', 'refresh');
?>
<style>
  .loginBox{
    width: 500px;
    min-height: 150px;
    padding: 10px;
    margin: 0px auto;
    margin-top: 150px;
    text-align: center;
    border:1px solid #CCC;
    border-radius: 5px;
    -webkit-box-shadow: 0px 0px 2px 1px rgba(216,216,216,.6);
    -moz-box-shadow: 0px 0px 2px 1px rgba(216,216,216,.6);
    box-shadow: 0px 0px 2px 1px rgba(216,216,216,.6);
  }
</style>
<div class="container">
	<div class="row">
		<div class="loginBox">
			<br>
			<h4>Successfully logged out</h4>
			<br>
			<a href="../../login">Click to login back!</a>
		</div>
	</div>
</div>