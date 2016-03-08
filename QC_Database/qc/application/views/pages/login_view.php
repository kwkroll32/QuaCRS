<?php
  $head['title'] = 'QuaCRS';
  $navbar['selected']='Null';
  $this->load->view('templates/head', $head);
  $this->load->view('templates/header', $navbar);
?>
<style>
  .loginBox{
    width: 500px;
    min-height: 280px;
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
        <h3>Login to QuaCRS</h3>
        <br>
        <?php echo validation_errors(); ?>
        <?php echo form_open('verifylogin'); ?>
          <input class="form-control" type="text" size="20" placeholder="Username" id="username" name="username"/>
          <br/>
          <input class="form-control" placeholder="Password" type="password" size="20" id="password" name="password"/>
          <br/>
          <br/>
          <input class="btn btn-success" type="submit" value="Sign In"/>
        <?php echo form_close(); ?>
    </div>
  </div>
</div>
<?php
  $this->load->view('templates/footer');
?>

