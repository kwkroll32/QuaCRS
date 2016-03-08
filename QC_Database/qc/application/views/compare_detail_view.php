<?php
    /*
     * @author Taha Mazher Topiwala
     */

    $base_url = $this->config->item('base_url');
    $resources = $this->config->item('resources');
    $precision = $this->config->item('precision');
?>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript" src="<?=$base_url?>assets/plugins/jquery_sparkline/js/jquery.sparkline.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script type="text/javascript" src="<?=$resources?>js/graphSingleMetric.js"></script>
<script type="text/javascript" src="<?=$resources?>js/magicalVanish.js"></script>
<script type="text/javascript" src="<?=$resources?>js/saveCompare.js"></script>
<link href="<?php echo $resources;?>css/saveCompare.css" rel="stylesheet">
<script type="text/javascript">
    $(function() {
        $('.sparklines').sparkline('html', {type: 'box', width: '4em', tooltipFormatFieldlist: ['lw', 'lq', 'med', 'uq', 'rw'], tooltipFormatFieldlistKey: 'field'});
    });
</script>
<link href="<?php echo $resources;?>css/groupcomparison.css" rel="stylesheet">
<script src="<?php echo $resources;?>js/groupcomparison.js" type="text/javascript"></script>

<?php
    echo "<style>";
    for($i = 0; $i < count($groupArrayColor); $i++){
        echo <<<EOF
            .color-ball-$i{
                display:inline-block;
                width:20px;
                height:20px;
                text-align:center;
                border-radius:50%;
                border:3px solid $groupArrayColor[$i];
                background-color:$groupArrayColor[$i];
            }
            .color-$i{
                border:2px solid $groupArrayColor[$i];
            }

            .color-thin-$i{
                height:50px;
                border-bottom:2px solid $groupArrayColor[$i];
            }

            .font-color-$i{
                color: $groupArrayColor[$i];
            }
EOF;
    }
    echo "</style>";
?>

<?php
    // Hidden Tables.
    $viewHiddens=array("duplication","expression","general","gC_Content","splicing","variants");
    function reformat_number($output, $precision){

        $this_exploded = explode('.',$output);
        $this_length = strlen($this_exploded[0]);
        if($this_length>=3){
            echo number_format($output);
        }elseif($this_length==2){
            echo number_format($output,1);
        }elseif($this_length==1){
            echo number_format($output,2);
        }else{
            echo number_format($output,$precision);
        };

    }
    // Setting certain tabes to be hidden by default. Those table being (duplication, expression, gC_Content)
    foreach($viewNames as $view){
        if(!in_array($view, $viewHiddens)){
            $allTables[$view] = true;
        }else{
            $allTables[$view] = false;
        }
    }
?>
<div class='container'>
    <div class='row'>
        <div class='topHalf'>
            <div class='banner'><p>Group Summary</p></div>
            <?php
            for($i=0; $i < $groupcount; $i++){
                    $sampleCount = sizeof($sampleNames[$i]);
                    $groupname = $groupArrayName[$i];
                echo <<<EOF
                    <div class="groupblock color-$i">
                        <div class = "nameGroup font-color-$i">
                            <p>$groupname</p>
                        </div>
                        <div class ="groupSummary">
                            <p>Sample Count : $sampleCount</p>
                        </div>
                        <div class= "groupInfo">
EOF;
                            for($j = 0; $j < $sampleCount; $j++){
                                echo "<p onClick = 'showSampleInfo(this)' sample-id='".$sampleIdArray[$i][$j]."'>".$sampleNames[$i][$j]."</p>";
                            }
                echo <<<EOF
                        </div>
                    </div>
EOF;
            }
        ?>
        <div>
            <?php
                if(!$ANOVAPresence){
                    echo "<div class='information'>";
                        echo "<p>Due to insufficient samples the <i>p</i> value could not be calculated</p>";
                    echo "</div>";
                }
            ?>
        </div>
        </div>

        <script type="text/javascript">
            var MasterGroupNames = <?=json_encode($groupArrayName)?>;
            var MasterArrayedGroupSampleNames = <?=json_encode($sampleNames)?>;
            var MasterSingleGraphSampleNames = <?=json_encode($singleGraphSampleNames)?>;
            var MasterColor = <?=json_encode($groupArrayColor)?>;
            var MasterViewNameColumn = <?=json_encode($allColumns)?>;
            var MasterGroupWithID = <?=json_encode($MasterGroupWithID)?>;
        </script>

        <div class="header">
            <?php
                $hiddenString = array('id' => $idString);
                echo form_open('ajax/generate_report', "", $hiddenString);
            ?>
                <button class="btn btn-success" name="submit" style='float:right' title="Download Compare Results"><span class='fa fa-download'></span></button>
            <?php
                echo form_close();
            ?>
            <p style='float:left;cursor: pointer'>Comparison Results</p>
        </div>
        <div class="tabBarToggle" id="togglebar">
            <div class="toggleButton"  onClick="toggleDataView(this)" data-toggle = "1" style="border-color:#00bcd4">
                <p>Table Data</p>
            </div>
            <div class="toggleButton" onClick="toggleDataView(this)" data-toggle = "2">
                <p>Graph Data</p>
            </div>
        </div>
        <div class="bottomHalf" id="bottomHalfContent">
            <div class="infoContainer showContent" id="infoContainerOne">
                <div class="menu" id="menu">
                    <div class="viewTables">
                        <div class="topBanner"><p>Jump to a table</p>
                        </div>
                        <div class="linkHold">
                            <a href="#FastQCMoveHere">FastQC Stat</a>
                        </div>
                    <?php
                        foreach($allTables as $viewName => $shown){
                            echo '<div class="linkHold">';
                                echo '<a href="#'.$viewName.'" id="jumpToViewName_'.$viewName.'">'.ucfirst(str_replace("_"," ",$viewName)).'</a>';
                            echo "</div>";
                        }
                    ?>
                    </div>
                    <div class="viewSettings">
                        <div class="topBanner"><p>View Settings</p></div>
                        <div class="viewSetting-content">
                          <div class="viewSettingsRow" onclick="openSaveCompareBackDrop(this)">
                              <p id="magicalVanishTableText">Save Compare</p>
                          </div>
                          <div class="viewSettingsRow" onclick="vanishTables(this)" data-magictoggle="vanish">
                              <p id="magicalVanishTableText">Hide all 'Null' Tables</p>
                          </div>
                          <div class="viewSettingsRow" onclick="vanishMetrics(this)" data-magictoggle="vanish">
                              <p id="magicalVanishMetricText">Hide all 'Null' Metrics</p>
                          </div>
                          <div class="viewSettingsRow" onclick="collapseAllTables(this)" data-collapsetoggle="collapse">
                              <p id="collapseAllTablesText">Collapse all Tables</p>
                          </div>
                        </div>
                    </div>
                </div>
                <div class='fastqcContainer' id="FastQCMoveHere">
                    <div class='topBanner'>
                        <button class="viewNameButtonTable" id="fastqcDataTableButton" data-toggle = "close" target = "fastqcData" onclick="collapseViewIndividual(this);">Collapse Table</button>
                        <p style='float:left'>FastQC Stat</p>
                    </div>
                    <div class='fastqcData' id="fastqcData">
                        <div class="table" id="FastQC_Stats_Table_Data">
                            <div class="tableHeader border-bottom-light">
                                <div class="tableColumn" metric><p>Metric</p></div>
                                <div class="tableColumn"><p>Color</p></div>
                                <div class="tableColumn"><p>Fail</p></div>
                                <div class="tableColumn"><p>Warn</p></div>
                                <div class="tableColumn"><p>Pass</p></div>
                            </div>
                            <div class="tableBody">
                                <?php
                                $keepRowOpen = true;
                                foreach($fastqc_aggregate_result[0] as $key=>$val){
                                    echo "<div class='tableRow border-bottom-light'>";

                                       $collapse = ($keepRowOpen? 'toggle = "close" open' : 'toggle = "open" close');

                                        echo "<div onClick='expandInt(this)' target='fast_qc_fail_pass_warn_$key' $collapse class='border-right design-font tableColumn' metric>".ucfirst(str_replace("_"," ",$key))."</div>";
                                        $calculate = $fastqc_aggregate_result_cumulative[$key];
                                        $fail = $calculate['fail'];
                                        $warn = $calculate['warn'];
                                        $pass = $calculate['pass'];
                                        $total = $fail + $warn + $pass;
                                        if ($total == 0){
                                            $ratio = 0;
                                        }else{
                                            $ratio = ($val['pass']/$total*100);
                                        }
                                        echo "<div class='tableColumn'>Cumulative</div>";
                                        echo "<div class='tableColumn ".(($fail==$total)?"danger":"")."'>".$fail."</div>";
                                        echo "<div class='tableColumn ".(($warn==$total)?"warning":"")."'>".$warn."</div>";
                                        echo "<div class='tableColumn ".(($ratio == 100)?"success":"")."'>".$pass."/".$total." (".number_format(($ratio),2)."%)</div>";
                                            echo "<div class='tableColumnWrap' id='fast_qc_fail_pass_warn_$key' $collapse>";
                                            for($i = 0; $i < count($fastqc_aggregate_result); $i++){
                                                $calculate = $fastqc_aggregate_result[$i][$key];
                                                $groupname = $groupArrayName[$i];
                                                echo "<div class='tableRow' id='toggler_$key'>";
                                                    echo "<div class='border-right tableColumn' id= 'groupNamePrimary_$i' metric>$groupname</div>";
                                                    $fail = $calculate['fail'];
                                                    $warn = $calculate['warn'];
                                                    $pass = $calculate['pass'];
                                                    $total = $fail + $warn + $pass;
                                                    if ($total == 0){
                                                            $ratio = 0;
                                                    }else{
                                                            $ratio = ($val['pass']/$total*100);
                                                    }
                                                    echo "<div class = 'tableColumn'  title='Group $i'><div class='color-ball-$i'></div></div>";
                                                    echo "<div  class='tableColumn ".(($fail==$total)?"danger":"")."'>".$fail."</div>";
                                                    echo "<div  class='tableColumn ".(($warn==$total)?"warning":"")."'>".$warn."</div>";
                                                    echo "<div class='tableColumn ".(($ratio == 100)?"success":"")."'>".$pass."/".$total." (".number_format(($ratio),2)."%)</div>";
                                                echo "</div>";
                                            }
                                            echo "</div>";
                                    echo "</div>";
                                    $keepRowOpen = false;
                                }
                                ?>
                            </div> <!-- Close of Table Body -->
                        </div> <!-- Close of Table -->
                    </div>
                </div>
                <!-- All other QC STATS -->
                <div class="allStats">
                    <?php
                        foreach($allTables as $viewName => $shown){
                            echo "<div class='viewHold' id='".$viewName."'>";
                                echo "<div class='topBanner'>";
                                    echo '<button class="viewNameButtonTable" style='.($shown ? '"border-color:green"' : '""').' data-toggle = '.($shown ?'close':'open').' target = "table_'.$viewName.'" id= "btn_'.$viewName.'" onclick="collapseViewIndividual(this)">'.($shown ?'Collapse Table':'Open Table').'</button>';
                                    echo "<p style='float:left'>".ucfirst(str_replace("_"," ",$viewName))."</p>";
                                echo "</div>";
                                echo "<div class='tableData' id='table_$viewName' style = '".($shown ? '': 'height:0px')."' onload = 'supportHeight(this);'>";
                                    echo "<div class='table'>";
                                        echo "<div class='tableHeader border-bottom-light'>";
                                            echo <<<EOF
                                                <div class="tableColumn" metric-small><p>Metric</p></div>
                                                <div class="tableColumn"><p>Misc</p></div>
                                                <div class="tableColumn"><p>Min</p></div>
                                                <div class="tableColumn"><p>Avg</p></div>
                                                <div class="tableColumn"><p>Max</p></div>
                                                <div class="tableColumn"><p>Plot</p></div>
EOF;
                                        echo "</div>";
                                        echo "<div class='tableBody'>";
                                        // Will now print the table data in accordance to group
                                                $plot_data = array();
                                                if ($viewName != "fastqc_stats"){
                                                    for ($j=0;$j< count($plot_info_cumulative[$viewName]);$j++){
                                                        foreach($plot_info_cumulative[$viewName][$j] as $key=>$value){
                                                            if(isset($plot_data[$key])){
                                                                $plot_data[$key] .= ", ".$value;
                                                            }
                                                            else{
                                                                $plot_data[$key] = $value;
                                                            }
                                                        }
                                                    }
                                                }
                                                $keyPrinted = false;
                                                $printGroupNumber = false;
                                                $printMin = false;
                                                $printMax = false;
                                                $printPlot = false;

                                                $carrier = array();
                                                $keepRowOpen = true;

                                                /*
                                                    Preparing Magical Vanish Row
                                                */

                                                $vanishStyleTable = true;

                                                foreach ($aggregate_result_views_cumulative[$viewName] as $key => $value) {

                                                    $collapse = ($keepRowOpen? 'toggle = "close" open' : 'toggle = "open" close');
                                                    $collapseGraphRow = ($keepRowOpen? 'open' : 'close');

                                                    // $exploded = explode('_', $key,2);
                                                    // $replaced = str_replace(">","",$exploded[1]);
                                                    // $metric = str_replace( "%","",$replaced);
                                                    // $metric = str_replace( ">","",$metric);

                                                    $indentifyMetric = substr($key, 4);

                                                    /*
                                                        Check for magical vanish
                                                    */

                                                    $vanishRow = $magicalVanishMetric[$viewName][$indentifyMetric];
                                                    $vanishStyleRow = "";
                                                    if($vanishRow){
                                                        $vanishStyleRow = "data-magicalMetric = 'true'";
                                                    }else{
                                                        $vanishStyleRow = "data-magicalMetric = 'false'";
                                                        $vanishStyleTable = false;
                                                    }

                                                    if (!$keyPrinted){
                                                        if(strpos($key, "min") !== false){
                                                            $printMin = true;
                                                            $printGroupNumber = true;
                                                        }
                                                        if (array_key_exists("percent_".sha1(substr($key,4)),$flags)){

                                                            if($flags["percent_".sha1(substr($key,4))] === "true"){
                                                                $printFlag = "percent";
                                                            }else{
                                                                $printFlag = "decimal";
                                                            }

                                                        }else{
                                                            $printFlag = "int";
                                                        }
                                                        $carrier[0] = substr(str_replace("_"," ",$key),3);
                                                        echo '<div '.$vanishStyleRow.'><div class= "tableRow border-bottom-light">'
                                                            .'<div class="tableColumn design-font border-right" metric-small onClick="expandInt(this)" target="'.$indentifyMetric.'" '.$collapse.' >'.$carrier[0]."</div>";
                                                        $keyPrinted = true;
                                                    }

                                                    if($printGroupNumber){
                                                        if($ANOVAPresence){
                                                            $pvalue = $ANOVA[$viewName][$indentifyMetric];
                                                            if($pvalue < 0.05){
                                                                echo "<div class='tableColumn'><p style = 'color: red'><i>p</i> = $pvalue </p></div>";
                                                            }else{
                                                                echo "<div class='tableColumn'><p style = 'color: #FF9933'><i>p</i> = $pvalue </p></div>";
                                                            }
                                                        }else{
                                                            echo "<div class='tableColumn'></div>";
                                                        }
                                                        $printGroupNumber = false;
                                                    }

                                                    if ($printMin){
                                                        echo "<div class='tableColumn'>";
                                                        if ($printFlag == "percent")
                                                                echo number_format(($value*100), $precision). "%";
                                                        elseif($printFlag == "decimal")
                                                                echo reformat_number($value, $precision);
                                                        else
                                                                echo reformat_number($value, $precision);
                                                        echo "</div>";
                                                        $printGroupNumber = false;
                                                        $printAvg = true;
                                                        $printMax = false;
                                                        $printMin = false;
                                                        $printPlot = false;
                                                        $carrier[1] = $key; // min_*
                                                        continue;
                                                    }
                                                    if ($printAvg){
                                                        echo "<div class='tableColumn'>";
                                                            if ($printFlag == "percent"){
                                                                echo number_format(($value*100), $precision). "%";
                                                            }elseif($printFlag == "decimal"){
                                                                echo reformat_number($value, $precision);
                                                            }else{
                                                                echo reformat_number($value, $precision);
                                                            }
                                                        echo "</div>";
                                                        $printGroupNumber = false;
                                                        $printAvg = false;
                                                        $printMax = true;
                                                        $printMin = false;
                                                        $printPlot = false;
                                                        $carrier[2] = $key; // avg_*
                                                        continue;
                                                    }
                                                    if ($printMax){
                                                        echo "<div class='tableColumn'>";
                                                        if ($printFlag == "percent")
                                                                echo number_format(($value*100), $precision). "%";
                                                        elseif($printFlag == "decimal")
                                                                echo reformat_number($value, $precision);
                                                        else
                                                                echo reformat_number($value, $precision);
                                                        echo "</div>";
                                                        $printGroupNumber = false;
                                                        $keyPrinted = false;
                                                        $printMin = false;
                                                        $printMax = false;
                                                        $printAvg = false;
                                                        $printPlot = true;
                                                        $carrier[3] = $key; // max_*
                                                    }

                                                    if ($printPlot){
                                                        echo "<div class='tableColumn'>";
                                                        $trim_key = substr($key, 4);
                                                        echo "<span class='sparklines'>$plot_data[$trim_key]</span>";
                                                        echo "</div></div>";
                                                        $printGroupNumber = false;
                                                        $printMin = false;
                                                        $printMax = false;
                                                        $printAvg = false;
                                                        $printPlot = false;
                                                    }

                                                    echo "<div class='tableColumnWrap' id='$indentifyMetric' ".$collapse.">";
                                                        //Printing Nested Group
                                                        for($i = 0; $i< $groupcount; $i++){

                                                            $groupname = $groupArrayName[$i];

                                                            /*Initialisiing Print Values*/
                                                            $keyPrinted = false;
                                                            $printGroupNumber = false;
                                                            $printMin = false;
                                                            $printMax = false;
                                                            $printPlot = false;
                                                            echo "<div class='tableRow'>";
                                                                echo "<div class='tableColumn border-right' metric-small>";
                                                                    echo "$groupname";
                                                                echo "</div>";
                                                                echo "<div class='tableColumn'>";
                                                                    echo "<div class='color-ball-$i'></div>";
                                                                echo "</div>";

                                                                    /*Check what type on value is needed*/

                                                                    if (array_key_exists("percent_".sha1(substr($key,4)),$flags )){
                                                                        if($flags["percent_".sha1(substr($key,4))] === "true"){
                                                                            $printFlag = "percent";
                                                                        }else{
                                                                            $printFlag = "decimal";
                                                                        }
                                                                    }else{
                                                                        $printFlag = "int";
                                                                    }
                                                                    /* Get Each Value for MIN AVG MAX For each Individual Group */

                                                                    $minValue = $aggregate_result_views[$i][$viewName][$carrier[1]];
                                                                    $avgValue = $aggregate_result_views[$i][$viewName][$carrier[2]];
                                                                    $maxValue = $aggregate_result_views[$i][$viewName][$carrier[3]];

                                                                    /* MIN VALUE PRINTING ALONG WITH REFORMATIG */

                                                                echo "<div class='tableColumn'>";
                                                                    if ($printFlag == "percent"){
                                                                        echo number_format(($minValue*100), $precision). "%";
                                                                    }elseif($printFlag == "decimal"){
                                                                        echo reformat_number($minValue, $precision);
                                                                    }else{
                                                                        echo reformat_number($minValue, $precision);
                                                                    }
                                                                echo "</div>";

                                                                    /* AVG VALUE PRINTING ALONG WITH REFORMATIG */

                                                                echo "<div class='tableColumn'>";
                                                                    if ($printFlag == "percent"){
                                                                        echo number_format(($avgValue*100), $precision). "%";
                                                                    }elseif($printFlag == "decimal"){
                                                                        echo reformat_number($avgValue, $precision);
                                                                    }else{
                                                                        echo reformat_number($avgValue, $precision);
                                                                    }
                                                                echo "</div>";

                                                                    /* MAX VALUE PRINTING ALONG WITH REFORMATIG */

                                                                echo "<div class='tableColumn'>";
                                                                    if ($printFlag == "percent"){
                                                                        echo number_format(($maxValue*100), $precision). "%";
                                                                    }elseif($printFlag == "decimal"){
                                                                        echo reformat_number($maxValue, $precision);
                                                                    }else{
                                                                        echo reformat_number($maxValue, $precision);
                                                                    }
                                                                echo "</div>";

                                                                /* PLOT DISPLAY USING SPARKLINES */

                                                                $plot_data_group = array();
                                                                if ($viewName != "fastqc_stats"){
                                                                    for ($j=0;$j< count($plot_info[$i][$viewName]);$j++){
                                                                        foreach($plot_info[$i][$viewName][$j] as $key=>$value){
                                                                            if(isset($plot_data_group[$key])){
                                                                                $plot_data_group[$key] .= ", ".$value;
                                                                            }else{
                                                                                $plot_data_group[$key] = $value;
                                                                            }
                                                                        }
                                                                    }
                                                                }

                                                                echo "<div class='tableColumn'>";
                                                                    $trim_key = substr($carrier[3], 4);
                                                                    echo "<span class='sparklines'>$plot_data_group[$trim_key]</span>";
                                                                echo "</div>";

                                                            echo "</div> <!-- End of table row -->";
                                                        }
                                                        echo "<div class='tableRow'>";
                                                        if(!$vanishRow){
                                                            $dataX = $singleGraphValue[$viewName][$indentifyMetric];

                                                            /*echo "<div class='graphingHeader design-font' onClick = 'openGraphRow(this)' data-view='$indentifyMetric' $collapseGraphRowToggle>";
                                                                echo "<p>Graph</p>";
                                                            echo "</div>";*/

                                                            if(strpos($indentifyMetric, '<') !== false || strpos($indentifyMetric, '%') !== false || strpos($indentifyMetric, '>') !== false){
                                                              $randomNumber = rand(20,4000);
                                                              $indentifyMetric = str_replace(">",$randomNumber,$indentifyMetric);
                                                              $indentifyMetric = str_replace("<",$randomNumber,$indentifyMetric);
                                                              $indentifyMetric = str_replace( "%",$randomNumber,$indentifyMetric);
                                                            }
                                                            echo "<div class='graphingRow' id='graphingRow_".$indentifyMetric."' data-x = '".json_encode($dataX)."' data-view = '$indentifyMetric' data-title = '$carrier[0]'>";
                                                                echo "<div class='graphBlock' id = 'graphPresentBlock_".$indentifyMetric."'></div>";
                                                            echo "</div>";
                                                            echo <<<EOS
                                                                <script>
                                                                    fireUpGraph("$indentifyMetric");
                                                                </script>
EOS;
                                                        }else{
                                                            echo "<div class='information'>";
                                                                echo "<p>Due to presence of Missing or 0's values the Graph could not be plotted for $carrier[0].</p>";
                                                            echo "</div>";
                                                        }
                                                        echo "</div>";
                                                    echo "</div></div>";
                                                    $keepRowOpen = false;
                                                }
                                                if($vanishStyleTable){
                                                    echo <<<EOF
                                                        <script>
                                                            addMagicalTableAttribute("$viewName");
                                                        </script>
EOF;
                                                }
                                                $vanishStyleTable = true;
                                        echo "</div> <!-- Close of Table Body -->";
                                    echo "</div> <!-- Close of Table -->";
                                echo "</div>";
                            echo "</div>";
                        }
                    ?>
                </div>
            </div>
            <div class="infoContainer hideContent" id="infoContainerTwo">
                <div class="graphBoxHeader">
                    <div class="selectBox">
                        <div class="select-style" id="yAxisOptionDropDown">
                              <select id="yAxis" name = "yValue" title="y" onchange="yAxis(this)">
                                <option title="y" value="0">Y - Value</option>
                                <?php
                                    foreach ($viewNameAndColumnName as $viewName => $columns) {
                                        if($viewName != "fastqc_stats"){
                                            echo "<optgroup label='".ucfirst(str_replace("_"," ",$viewName))."'>";
                                            foreach ($columns as $column) {
                                                if($column['Field'] != "qcID"){
                                                    echo  "<option value ='".$column['Field']."' data-hold= '".json_encode($singleGraphValue[$viewName][$column['Field']])."' >".$column['Field']."</option>";
                                                }
                                            }
                                            echo "</optgroup>";
                                        }
                                    }
                                ?>
                              </select>
                        </div>
                    </div>
                    <div class="selectBox">
                        <div class="select-style" id="xAxisOptionDropDown">
                              <select id="xAxis" name = "xValue" title="x" onchange="xAxis(this)">
                                <option title="x" value="0">X - Value</option>
                                <?php
                                    foreach ($viewNameAndColumnName as $viewName => $columns) {
                                        if($viewName != "fastqc_stats"){
                                            echo "<optgroup label='".ucfirst(str_replace("_"," ",$viewName))."'>";
                                            foreach ($columns as $column) {
                                                if($column['Field'] != "qcID"){
                                                    echo  "<option value ='".$column['Field']." ' data-hold= '".json_encode($singleGraphValue[$viewName][$column['Field']])."' >".$column['Field']."</option>";
                                                }
                                            }
                                            echo "</optgroup>";
                                        }
                                    }
                                ?>
                              </select>
                        </div>
                    </div>
                </div>
                <div class="multipleGraphViewContainer" id="multipleGraphViewContainer">

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Template for Overlay Display (Single Sample View) -->

<div class="SampleDisplayBackDrop" id ="SampleDisplayBackDrop">
    <div class='head'>
        <p>Single Sample</p>
        <button class='btn btn-primary' onclick='closeDrop(); return false;'>Close</button>
    </div>
    <div class='backdropSingleContent' id='backdropSingleContent'></div>
</div>

<!-- Save Compare Modal-->

<div class="saveCompareBackDrop">
  <div class="saveCompareBackContainer">
    <div class="saveCompareContainer">
      <div class="saveCompareheader">
          <p>Save Compare</p>
      </div>
      <div class="saveCompareContent">
        <input class="form-control" placeholder="Compare Name" id='compareName'/>
        <div>
          <button class="utility-option-button" red  onclick="closeSaveCompareBackDrop()">Close</button>
          <button class="utility-option-button" onclick="performSave()" >Save</button>
        </div>
      </div>
    </div>
  </div>
</div>
