<?php
    $base_url = $this->config->item('base_url');
    $resources = $this->config->item('resources');
?>
        <style>
            .scrollUp{
                display: none;
                position:fixed;
                width: 80px;
                height: 70px;
                bottom: 0;
                left: 0;
                text-align: left;
                z-index: 9999;
                padding-left: 10px;
            }

            .button-rounded{
                width: 50px;
                height: 50px;
                background: none;
                background-color: white;
                border:2px solid green;
                outline:0;
                color: green;
                border-radius: 50%;
                margin: 0px auto;
            }
        </style>
        <div class='scrollUp'>
            <button class="button-rounded" onclick="scrollupPrimary()"><span class="fa fa-chevron-up"></span></button>
        </div>
        <div id="footer">
    	    <div class="container">
                <p class="text-muted">OSUCCC | Illumina Sequencing Core</p>
    	    </div>
	   </div>
        <script src="<?php echo $resources;?>plugins/bootstrap/js/bootstrap.min.js"></script>
        <script src="<?php echo $resources;?>plugins/bootstrap/js/holder.js"></script>
        <script src="<?php echo $resources;?>plugins/dragtable/js/dragtable.js"></script>
	    <script src="<?php echo $resources;?>plugins/jquery_sparkline/js/jquery.sparkline.min.js"></script>
        <script src="<?php echo $resources;?>plugins/tablesorter/js/tablesorter.min.js"></script>
        <script src="<?php echo $resources;?>plugins/overlay/itpoverlay.js"></script>
        <script src="<?php echo $resources;?>plugins/agg_plts.js"></script>
        <script src="<?php echo $resources;?>plugins/fancybox/jquery.fancybox.pack.js"></script>
        <script src="<?php echo $resources;?>plugins/jquery_cookie/jquery.cookie.js"></script>
        <script src="<?php echo $resources;?>js/script.js"></script>
    </body>
</html>
