<?php
$base_url = $this->config->item('base_url');
$precision = $this->config->item('precision');
$resources = $this->config->item('resources');
//$viewNames = array("fastqc_stats","GC_content","alignment_stats","genomic_stats", "library_stats", "strand_stats");
$viewHiddens=array("mapping_duplicates", "sequence_duplicates");
$staticTables = array();
foreach($viewNames as $view){
	if(!in_array($view, $viewHiddens)){
		$staticTables[$view] = $view;
	}
}
?>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div>
				<h3><?=$sample?> Details:</h3>
				
				<?php $hidden = array('id' => $qcID);
				echo form_open('ajax/generate_report', "", $hidden);?>
					<input type="submit" name="submit" id="download-report" class="btn btn-success btn-sm" value="Download Report">
				<?php form_close();?>

			</div>
			<div class="row">
				<?php foreach($staticTables as $viewName){
					echo "<div class='col-md-6'>";
						echo "<div class='row'>";
							echo "<div class='col-md-6'>";
								if ($viewName == "fastqc_stats"){
									echo "<h4>FastQC Stats</h4>";
								}
								else{
									echo "<h4>".ucfirst(str_replace("_"," ",$viewName)). "</h4>";
								}
							echo "</div>";
							echo "<div class='col-md-6'>";
								echo "<button type='button' id='btn_".$viewName."' class='pull-right btn btn-info btn-sm' onclick='toggle_table(\"".$viewName."\")'>Hide</button>";
							echo "</div>";
						echo "</div>";
						echo "<div id='".$viewName."'>";
							echo "<table  class='table table-hover table-bordered'><thead>";
								echo "<tr><th>Metric</th><th>Value</th></tr></thead><tbody>";
								foreach($views[$viewName] as $key=>$value){
									if ($key == "qcID")
										continue;
									
									echo "<tr><td>". str_replace("_"," ",$key) ."</td>";
									if (strtolower($value) == "fail"){
										echo "<td class='danger'>$value</td>";
									}
									elseif(strtolower($value)=="warn"){
										echo "<td class='warning'>$value</td>";
									}
									elseif (strtolower($value) == "pass"){
										echo "<td class='success'>$value</td>";
									}
									elseif($value == ""){
										echo "<td></td>";
									}
									else{
										echo "<td>";
										if (array_key_exists("percent_".sha1($key),$flags )){
											if($flags["percent_".sha1($key)] === "true"){
												echo number_format(($value*100), $precision). "%";
											}
											else{
												echo number_format($value, $precision);
											}
										}
										else{
											echo number_format($value);
										}
										echo "</td>";
									}
									
									echo "</tr>";
								}
							echo "</tbody></table>";
						echo "</div>";
					echo "</div>";
				}?>
				<div class="col-md-12">
					<div class="panel-group accordion"  id="duplicate_stat">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#duplicate_stat" href="#duplicatin_stats">
									Duplicates Stats
								</a>
								</h4>
							</div>
							<div id="duplicatin_stats" class="panel-collapse collapse in">
								<div class="panel-body">
									<?php foreach($viewHiddens as $viewName){
										echo "<div class='col-md-6'>";
											echo "<h4>".ucfirst(str_replace("_"," ",$viewName)). "</h4>";
											echo "<table class='table table-hover table-bordered'><thead>";
												echo "<tr><th>Metric</th><th>Value</th></tr></thead><tbody>";
												foreach($views[$viewName] as $key=>$value){
													if ($key == "qcID")
														continue;
													echo "<tr><td>". str_replace("_"," ",$key) ."</td><td>".number_format($value)."</td></tr>";
												}
											echo "</tbody></table>";
										echo "</div>";
									}?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-12">
					<div class="panel-group accordion" id="plots">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#plots" href="#plots_images">
									Plots
								</a>
								</h4>
							</div>
							<div id="plots_images" class="panel-collapse collapse in">
								<div class="panel-body">
									<div class='row'>
										<?php
										foreach($img as $image){
											if ($image == "")
												continue;
												
											echo "<div class='col-xs-6 col-md-3'>";
												echo "<a href='".$resources."img/".$image."' class='thumbnail fancybox' rel='plots'>";
													if (strpos($image, ".pdf") !== false)
														echo "<embed src='".$resources."img/".$image."' width='244' height='183' >";
													else
														echo "<img src='".$resources."img/".$image."' alt=''>";
													
												echo "</a>";
											echo "</div>";
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
