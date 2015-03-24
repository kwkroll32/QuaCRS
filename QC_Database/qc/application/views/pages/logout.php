<?
$base_url = $this->config->item('base_url');
$precision = $this->config->item('precision');
$resources = $this->config->item('resources');

$this->session->unset_userdata('logged_in');
#session_destroy();
//redirect('home', 'refresh');
?>

<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<br>
			<h4>Successfully logged out</h4>
			<br>
			<a href="../../login">Login</a>
		</div>
	</div>
</div>