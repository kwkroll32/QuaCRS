<?
  $head['title'] = 'login';
  $navbar['selected']='Null';
  $this->load->view('templates/head', $head);
  $this->load->view('templates/header', $navbar);
  //$this->load->view('pages/'.$page);
  $this->load->view('templates/footer');
?>

<div class="container">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <h1>Login</h1>
      <?php echo validation_errors(); ?>
      <?php echo form_open('verifylogin'); ?>
        <label for="username">Username:</label>
        <input type="text" size="20" id="username" name="username"/>
        <br/>
        <label for="password">Password :</label>
        <input type="password" size="20" id="passowrd" name="password"/>
        <br/>
        <input type="submit" value="Login"/>
      </form>
    </div>
  </div>
</div>

