<?php
$base_url = $this->config->item('base_url');
$precision = $this->config->item('precision');
$resources = $this->config->item('resources');
//hard coding which tables start hidden is still necessary for now.
$viewHiddens=array("fastQC_Stats");
$staticTables = array();
foreach($viewNames as $view){
    if(!in_array($view, $viewHiddens)){
        $allTables[$view] = true;
    }
    else{
        $allTables[$view] = false;
    }
}

function reformat_number($output, $precision){
    $this_length = strlen(explode('.', $output)[0]);
    if($this_length>=3){echo number_format($output);}elseif($this_length==2){echo number_format($output,1);}elseif($this_length==1){echo number_format($output,2);}else{echo number_format($output,$precision);};
}
//prints all the tables with clickable buttons to collapse them
function shown_block($views, $flags, $precision, $viewName, $shown){
	echo "<div class='col-md-6'>";
		echo "<div class='row'>";
			echo "<div class='col-md-6'>";
				if ($viewName == "fastQC_Stats"){
					echo "<h4>FastQC Stats</h4>";
				}
				else{
					echo "<h4>".ucfirst(str_replace("_"," ",$viewName)). "</h4>";
				}
			echo "</div>";
			echo "<div class='col-md-6'>";
				echo "<button type='button' data-toggle='collapse' data-target='#".$viewName."' data-parent='#accordion' id='btn_".$viewName."' class='pull-right btn btn-info btn-sm' onclick='change_val(\"".$viewName."\")'>"; if(!$shown){echo "Show";} else{echo "Hide";} echo "</button>";
			echo "</div>";
		echo "</div>";
		#important line below. The If-statement in this line determines which tables start closed.
		echo "<div class = 'table panel-collapse collapse"; if (!$shown){echo " in";}; echo "' id='".$viewName."'>";
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
							echo reformat_number($value, $precision);
						}
						echo "</td>";
					}
					
					echo "</tr>";
				}
			echo "</tbody></table>";
		echo "</div>";
	echo "</div>";
}

//this function is unused. This creates tables under drop down banners that start closed.
function hidden_block($views, $viewName){
	$name = ucfirst(str_replace("_"," ",$viewName));
	echo "<div class='col-md-12'>";
		echo "<div class='panel-group accordion'  id='duplicate_stat'>";
			echo "<div class='panel panel-default'>";
				echo "<div class='panel-heading'>";
					echo "<h4 class='panel-title'>";
					echo "<a data-toggle='collapse' data-parent='#duplicate_stat' href='#duplicatin_stats_$viewName'>";
						echo "$name";
					echo "</a>";
					echo "</h4>";
				echo "</div>";
				echo "<div id='duplicatin_stats_$viewName' class='panel-collapse collapse in'>";
					echo "<div class='panel-body'>";
						echo "<div class='col-md-6'>";
							echo "<table class='table table-hover table-bordered'><thead>";
								echo "<tr><th>Metric</th><th>Value</th></tr></thead><tbody>";
								foreach($views[$viewName] as $key=>$value){
									if ($key == "qcID")
										continue;
									echo "<tr><td>". str_replace("_"," ",$key) ."</td><td>".number_format($value)."</td></tr>";
								}
							echo "</tbody></table>";
						echo "</div>";
					echo "</div>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</div>";
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
				<?php 
					foreach($allTables as $viewName => $shown){
						shown_block($views, $flags, $precision, $viewName, $shown);
					}
				?>
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
<script>
//alternates the text on the "show/hide" buttons
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
