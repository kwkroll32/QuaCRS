<?
$base_url = $this->config->item('base_url');
$precision = $this->config->item('precision');
?>
<div class="container">
	<?echo form_open('search/presearch');?>
	<div class="row">
		<div class="col-md-6">
			<h3>Search Bar</h3>
			<div class="input-group">
				<input class="form-control" id="search-bar" name="keyword" type="text" <?=(isset($keyword)?"value='$keyword'":"");?> placeholder="Search box">
				<div class="input-group-btn">
					<button type="submit" name="submit" id="search-key" class="btn btn-default" tabindex="-1">Search</button>
					<!--<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" tabindex="-1">
						<span class="caret"></span>
						<span class="sr-only">Toggle Dropdown</span>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li><a href="#">Search Study</a></li>
						<li><a href="#">Other kinds of search</a></li>
						<li><a href="#">Something else here</a></li>
					</ul>-->
				</div>
			</div><!-- /.input-group -->
		</div>
		<div class='col-md-4 col-md-offset-2'>
			<h3>Select Columns</h3>
			<div class="panel-group">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
						<a data-toggle="collapse" href="#collapseOne">
							Columns
						</a>
						</h4>
					</div>
					<div id="collapseOne" class="panel-collapse collapse in">
						<div class="panel-body columns-accordion-body">
							<!--<ul class='list-unstyled'>-->
							<?
							$i=0;
							foreach($view as $viewName => $viewDetail){?>
								<div class="panel-group">
									<div class="panel panel-default">
										<div class="panel-heading">
											<h5 class="panel-title">
												<a data-toggle="collapse" href="#<?=$viewName?>"><?=ucwords(str_replace("_"," ",$viewName))?></a>
											</h5>
										</div>
										<div id="<?=$viewName?>" class="panel-collapse collapse in">
											<div class="container">
												<ul class='list-unstyled'>
												<?
												foreach($viewDetail as $viewColumn){
													if($viewColumn['Field'] == "qcID")
														continue;

													#if (in_array($viewColumn['Field'],$defaultColumns))
													if (in_array($i, $defaultColumns,true))
														$checked = "checked";
													else
														$checked = "";
													echo "<li><input type='checkbox' name='column-".($i)."' value='".$viewColumn['Field']."' class='column-name' name='".$viewColumn['Field']."' ".$checked."  onclick='toggle_column(".($i++).")' > ".str_replace("_"," ",$viewColumn['Field'])."</li>";	

												}
												?>
												</ul>
											</div>
										</div>
									</div>
								</div>
							<?}?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?echo form_close();?>

		<?echo form_open('sample/aggregate');?>
			<div class="col-md-12">
				<div class="row">
					<div class="col-md-6">
						<h3>Sample View</h3>
					</div>
					<div class="col-md-6">
						<input type="submit" value="Aggregate" name="aggregate" class="pull-right btn btn-info">
					</div>
				</div>
			
				<table id="samples" class="table table-hover table-bordered draggable sortable">
					<thead>
						<tr>
							<th><input type="checkbox" id="toggleCheckbox" data-checked="false"></th>
						<?
						$i=0;
						foreach($columns as $column){
							#if (in_array($column['Field'],$defaultColumns))
							
							if (in_array($i, $defaultColumns,true)){
								$style = "";
							}
							
							else
								$style = "hidden";

							#if ($column['Field'] == "Sample"){
							#	echo "<th data-col-ind='".($i++)."' class='".$style."'><input type='checkbox' id='checkall'> ";
							#		echo str_replace("_"," ",$column['Field']);
							#	echo "</th>";
							#}
							#else{
								echo "<th data-col-ind='".($i++)."' class='".$style."'>";
									echo str_replace("_"," ",$column['Field']);
								echo "</th>";		
							#}
							
						}

						?>
						</tr>
					</thead>
					<tbody>
					<?
					if (!empty($samples)){
						foreach($samples as $sample){
							echo "<tr >";
								echo "<td><input type='checkbox' class='sample-agg' name='sample-".($sample['qcID'])."' value='".$sample['qcID']."'></td>";
								//echo "<td class='col-md-1' onclick='return false;' ><input type='checkbox'/></td>";
								//echo "<td data-col-ind='0' class='' onclick='redirect_to(\"".$base_url."index.php/sample/detail/".$sample['qcID']."\")'>".$sample['qcID']."</td>";
								$i=0;
								foreach($columns as $column){
									#if (in_array($column['Field'],$defaultColumns))
									if(in_array($i, $defaultColumns,true))
										$style = "";
									else
										$style = "hidden";

									if ($column['Field'] != 'Sample'){
										echo "<td data-col-ind='".($i++)."' class='".$style."'>";
										
										if(is_numeric($sample[$column['Field']])){

											if (array_key_exists("percent_".sha1($column['Field']),$flags )){
												if($flags["percent_".sha1($column['Field'])] === "true"){
													echo number_format(($sample[$column['Field']]*100), $precision). "%";
												}
												else{
													echo number_format($sample[$column['Field']], $precision);
												}
											}
											else{
												
												echo number_format($sample[$column['Field']]);
											}
										}
										else{
											if (isset($keyword)){
												echo highlight_phrase($sample[$column['Field']], $keyword, "<span style='background-color:'>", "</span>");
											}
											else{
												echo $sample[$column['Field']];
											}
										}
											
										echo "</td>";
									}
									else{
										echo "<td data-col-ind='".($i++)."' class='".$style."'>";
											echo "<a href='".$base_url."index.php/sample/detail/".$sample['qcID']."'>" .((isset($keyword))?highlight_phrase($sample[$column['Field']],$keyword,"<span style='background-color:'>", "</span>"):$sample[$column['Field']]) ."</a>";
										echo "</td>";
									}
								}
							echo "</tr>";
						}
					}
					?>
					</tbody>
				</table>
			</div>
		<?echo form_close();?>
	</div>
</div>	
