<?php 
    $base_url = $this->config->item('base_url');
    $resources = $this->config->item('resources');
?>
		<div id="footer">
	      <div class="container">
	        <p class="text-muted">OSUCCC | Illumina Sequencing Core</p>
	      </div>
	    </div>
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="<?php echo $resources;?>plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo $resources;?>plugins/bootstrap/js/holder.js"></script>
        <script src="<?php echo $resources;?>plugins/dragtable/js/dragtable.js"></script>
	<script src="<?php echo $resources;?>plugins/jquery_sparkline/js/jquery.sparkline.min.js"></script>
        <!--<script src="<?php echo $resources;?>plugins/sorttable/js/sorttable.js"></script>-->
        <script src="<?php echo $resources;?>plugins/tablesorter/js/tablesorter.min.js"></script>
        <script src="<?php echo $resources;?>plugins/overlay/itpoverlay.js"></script>
        <script src="<?php echo $resources;?>plugins/fancybox/jquery.fancybox.pack.js"></script>
        <script src="<?php echo $resources;?>plugins/jquery_cookie/jquery.cookie.js"></script>
        <script src="<?php echo $resources;?>js/script.js"></script>
    </body>
</html>
