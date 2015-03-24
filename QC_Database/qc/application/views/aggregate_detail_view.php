<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script type="text/javascript" src="assets/plugins/jquery_sparkline/js/jquery.sparkline.min.js"></script>
<script type="text/javascript">$(function() {$('.sparklines').sparkline('html', {type: 'box', width: '4em', tooltipFormatFieldlist: ['lw', 'lq', 'med', 'uq', 'rw'], tooltipFormatFieldlistKey: 'field'});});</script>

<?
$base_url = $this->config->item('base_url'); 
$precision = $this->config->item('precision');
$viewNames = array("GC_content","alignment_stats","genomic_stats", "library_stats", "strand_stats");
$viewHiddens=array("mapping_duplicates", "sequence_duplicates" );
$samplesString = "";
foreach($samples as $sample){
	$samplesString .= $sample .',';
}
$samplesString = rtrim($samplesString,",");
?>

<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div>
				<h3>Aggregate Results:</h3>
				
				<?$hidden = array('id' => $samplesString);
				echo form_open('ajax/generate_report', "", $hidden);?>
					<input type="submit" name="submit" id="download-report" class="btn btn-success btn-sm" value="Download Report">
				<?=form_close();?>
			
			</div>
			<div class="row">
			<div class="col-md-6">
				<div class="row">
					<div class="col-md-6">
						<h4>FastQC Stats</h4>
					</div>
					<div class="col-md-6">
						<button type="button" id="btn_fastqc_stats" class="pull-right btn btn-info btn-sm" onclick="toggle_table('fastqc_stats')">Hide</button>
						<button type="button" id="btn_fastqc_stats_table" class="pull-right btn btn-success btn-sm" onclick="toggle_hidden_columns('fastqc_stats_table')">Show Details</button>
					</div>
				</div>
				<div id="fastqc_stats">
					<table id="fastqc_stats_table" class="table table-hover table-bordered">
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
			<?
			foreach($viewNames as $viewName){
				echo "<div class='col-md-6'>";
					echo "<div class='row'>";
						echo "<div class='col-md-6'>";
							echo "<h4>".ucfirst(str_replace("_"," ",$viewName)). "</h4>";
						echo "</div>";
						echo "<div class='col-md-6'>";
							echo "<button type='button' id='btn_".$viewName."' class='pull-right btn btn-info btn-sm' onclick='toggle_table(\"".$viewName."\")'>Hide</button>";
						echo "</div>";
					echo "</div>";
					echo "<div id='".$viewName."'>";
						echo "<table  class='table table-hover table-bordered'><thead>";
							echo "<tr><th>Metric</th><th>Min</th><th>Avg</th><th>Max</th><th>Plot</th></tr></thead><tbody>";
														
							$plot_data = array();
							if ($viewName != "fastqc_stats"){
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
									echo "<tr><td>". substr(str_replace("_"," ",$key), 3) ."</td>";
									$keyPrinted = true;
								}
								if ($printMin){
									echo "<td>";
									if ($printFlag == "percent")
										echo number_format(($value*100), $precision). "%";
									elseif($printFlag == "decimal")
										echo number_format($value, $precision);
									else
										echo number_format($value);
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
										echo number_format($value, $precision);
									else
										echo number_format($value);
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
										echo number_format($value, $precision);
									else
										echo number_format($value);
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
									#echo "<td>$value</td>";
								#echo "</tr>";
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
