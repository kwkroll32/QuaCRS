<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript" src="assets/plugins/jquery_sparkline/js/jquery.sparkline.min.js"></script>
<script type="text/javascript">
$(function() {
	$('.sparklines').sparkline('html', {type: 'box', width: '4em', tooltipFormatFieldlist: ['lw', 'lq', 'med', 'uq', 'rw'], tooltipFormatFieldlistKey: 'field'});
});
</script>
<script type="text/javascript">
    function change_val(buttonID)
    {
            if (document.getElementById("btn_"+buttonID).innerHTML == "Show"){
                    document.getElementById("btn_"+buttonID).innerHTML = "Hide";
            }
            else{
                    show = 1;
                    document.getElementById("btn_"+buttonID).innerHTML = "Show";
            }
    }
</script>
<style>
tr[id^="slidingDiv"]{
    height:auto;
    padding:5px;
    margin-top:5px;
    display:none;
    border:1px solid #333;
}
</style>


<?php
$base_url = $this->config->item('base_url'); 
$precision = $this->config->item('precision');
//$viewNames = array("GC_content","alignment_stats","genomic_stats", "library_stats", "strand_stats");
$viewHiddens=array("duplication","expression","gC_Content");
$samplesString = "";
foreach($samples as $sample){
	$samplesString .= $sample .',';
}
$samplesString = rtrim($samplesString,",");

function reformat_number($output, $precision){
	$this_length = strlen(explode('.', $output)[0]);
	if($this_length>=3){echo number_format($output);}elseif($this_length==2){echo number_format($output,1);}elseif($this_length==1){echo number_format($output,2);}else{echo number_format($output,$precision);};
}

foreach($viewNames as $view){
	if(!in_array($view, $viewHiddens)){
		$allTables[$view] = true;
	}
	else{
		$allTables[$view] = false;
	}
}
?>

<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div>
				<h3>Aggregate Results:</h3>
				
				<?php $hidden = array('id' => $samplesString);
				echo form_open('ajax/generate_report', "", $hidden);?>
					<input type="submit" name="submit" id="download-report" class="btn btn-success btn-sm" value="Download Report">
				<?php form_close();?>
			
			</div>
			<div class="row">
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-6">
						<h4>FastQC Stats</h4>
					</div>
					<div class="col-md-6">
						<button type="button" data-toggle='collapse' data-target='#fastQC_Stats' data-parent='#accordion' id="btn_fastQC_Stats" onclick="change_val('fastQC_Stats')" class="pull-right btn btn-info btn-sm">Hide</button>
						<button type="button" id="btn_fastQC_Stats_table" class="pull-right btn btn-success btn-sm" onclick="toggle_hidden_columns('fastQC_Stats_table')">Show Details</button>
					</div>
				</div>
				<div class = 'table panel-collapse collapse' id="fastQC_Stats">
					<table id="fastQC_Stats_table" class="table table-hover table-bordered">
						<thead>
							<tr><th>Metric</th><th data-secondary='true' class='hidden'>Fail</th><th data-secondary='true' class='hidden'>Warn</th><th>Pass</th></tr>
						</thead>
						<tbody>
						<?php
						foreach($fastqc_aggregate_result as $key=>$val){
							$total = $val['fail']+$val['warn']+$val['pass'];
							if ($total == 0)
								$ratio = 0;
							else
								$ratio = ($val['pass']/$total*100);

							echo "<tr>";
								echo "<td>".ucfirst(str_replace("_"," ",$key))."</td>";
								echo "<td data-secondary='true' class='".(($val['fail']==$total)?"danger":"")." hidden'>".$val['fail']."</td>";
								echo "<td data-secondary='true' class='".(($val['warn']==$total)?"warning":"")." hidden'>".$val['warn']."</td>";
								echo "<td class=".(($ratio == 100)?"success":"").">".$val['pass']."/".$total." (".number_format(($ratio),2)."%)</td>";
							echo "</tr>";
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
			foreach($allTables as $viewName=>$shown){

				echo "<div class='col-md-6'>";
					echo "<div class='row'>";
						echo "<div class='col-md-6'>";
							echo "<h4>".ucfirst(str_replace("_"," ",$viewName)). "</h4>";
						echo "</div>";
						echo "<div class='col-md-6'>";
							echo "<button type='button' id='btn_".$viewName."' class='pull-right btn btn-info btn-sm' data-toggle='collapse' data-target='#".$viewName."' data-parent='#accordion' onclick='change_val(\"".$viewName."\")'>"; if(!$shown){echo "Show";} else{echo "Hide";} echo "</button>";
						echo "</div>";
					echo "</div>";

					echo "<div class = 'table panel-collapse collapse in' id='".$viewName."'>";
					$script='$("#'.$viewName.'").removeClass(\'collapse in\');$("#'.$viewName.'").addClass(\'in\');';
					if ($shown){echo '<script>'.$script.';</script>';};

						echo "<table  class='table table-hover table-bordered'><thead>";
							echo "<tr><th>Metric</th><th>Min</th><th>Avg</th><th>Max</th><th>Plot</th></tr></thead><tbody>";
														
							$plot_data = array();
							if ($viewName != "fastQC_Stats"){
								//var_dump(json_encode($per_row_info[$viewName]) );
								for ($i=0;$i<count($plot_info[$viewName]);$i++){
									foreach($plot_info[$viewName][$i] as $key=>$value){
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
							$printMin = false;
							$printMax = false;
							$printPlot = false;
							
							foreach($aggregate_result_views[$viewName] as $key=>$value){
								// special characters are not permitted in HTML ID or NAME tags
								$metric = str_replace("%","",str_replace(">","",explode('_', $key,2)[1]));
								if (!$keyPrinted){
									if(strpos($key, "min") !== false){
										$printMin = true;
									}
									if (array_key_exists("percent_".sha1(substr($key,4)),$flags )){
										if($flags["percent_".sha1(substr($key,4))] === "true"){
											$printFlag = "percent";
										}
										else{
											$printFlag = "decimal";
										}
									}
									else{
										$printFlag = "int";
									}
									echo '<tr><td><a href="#" id="slidingDiv" class="show_hide" rel="#slidingDiv'.$metric.'">'. substr(str_replace("_"," ",$key), 3) ."</a></td>";
									$keyPrinted = true;
								}
								if ($printMin){
									echo "<td>";
									if ($printFlag == "percent")
										echo number_format(($value*100), $precision). "%";
									elseif($printFlag == "decimal")
										echo reformat_number($value, $precision);
									else
										echo reformat_number($value, $precision);
									echo "</td>";
									$printAvg = true;
									$printMax = false;
									$printMin = false;
									$printPlot = false;
									continue;
								}
								if ($printAvg){
									echo "<td>";
									if ($printFlag == "percent")
										echo number_format(($value*100), $precision). "%";
									elseif($printFlag == "decimal")
										echo reformat_number($value, $precision);
									else
										echo reformat_number($value, $precision);
									echo "</td>";
									$printAvg = false;
									$printMax = true;
									$printMin = false;
									$printPlot = false;
									continue;
								}
								if ($printMax){
									echo "<td>";
									if ($printFlag == "percent")
										echo number_format(($value*100), $precision). "%";
									elseif($printFlag == "decimal")
										echo reformat_number($value, $precision);
									else
										echo reformat_number($value, $precision);
									echo "</td>";
									$keyPrinted = false;
									$printMin = false;
									$printMax = false;
									$printAvg = false;
									$printPlot = true;
								}
								if ($printPlot){
									echo "<td>";
									$trim_key = substr($key, 4);
									echo "<span class='sparklines'>$plot_data[$trim_key]</span>";
									echo "</td></tr>";
									#$keyPrinted = false;
									$printMin = false;
									$printMax = false;
									$printAvg = false;
									$printPlot = false;

								}
								echo '<tr id="slidingDiv'.$metric.'"><td colspan="5" id="'.$metric.'_plt_row"></td></tr>';
								echo '<tr style="display: none;"><td colspan="5" id="'.$metric.'_plt_data">'.json_encode($per_row_info[$viewName]).'</td></tr>';
								#echo "<td>$value</td>";
								#echo "</tr>";
								#echo json_encode($per_row_info[$viewName]);
							}

						echo "</tbody></table>";

					echo "</div>";

				echo "</div>";

			}
			?>
			</div>
		</div>
	</div>
</div>

