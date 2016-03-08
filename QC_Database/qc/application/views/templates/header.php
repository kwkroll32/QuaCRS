<?php
    $base_url = $this->config->item('base_url');
    $projectName = $this->config->item('project_name');
?>
<link href="<?=$base_url?>assets/css/navheader.css" rel="stylesheet">
<section>
  <div class="navigationBar navigationBarExtended" id = "navigationbar">
      <div class="menuOptions">
        <div class="option">
          <a href="<?=$base_url."index.php/pages/view/aboutus"?>" >About</a>
        </div>
        <div class="option">
          <a href="<?=$base_url."index.php/pages/view/downloads"?>" >Downloads</a>
        </div>
        <div class="option">
          <a target = "_blank" href="<?=$base_url."index.php/pages/view/readme"?>" >Readme</a>
        </div>
        <?php
           if($this->session->userdata('logged_in'))
            {
                $session_data = $this->session->userdata('logged_in');
                $sdata['username'] = $session_data['username'];

                echo '<div class="option"><a href="'.$base_url.'index.php/pages/view/logout">Logout</a></div>';
             }
        ?>
      </div>
      <div class="projectName">
        <a href="<?php echo $base_url;?>"  id="projectName"><?=$projectName?></a>
      </div>
  </div>
</section>
