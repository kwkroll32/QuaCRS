<?php
        /*
         * @author Taha Mazher Topiwala
         */
        $base_url = $this->config->item('base_url');
        $precision = $this->config->item('precision');
        $resources = $this->config->item('resources');
        $xhr_root = $this->config->item('xhr_root');
?>
<link href="<?php echo $resources;?>css/columnstyles.css" rel="stylesheet">
<link href="<?php echo $resources;?>css/groupstyle.css" rel="stylesheet">
<link href="<?php echo $resources;?>css/demandsearch2.css" rel="stylesheet">
<script type="text/javascript">
        var xhr_root = "<?=$xhr_root?>";
        var Master_Search_Resulting_array = {};
        var Master_All_Columns = <?=json_encode($searchColumns)?>;
</script>
<div class="container">
        <div class="row">
          <div class="compareRowHeader">
              <div class="utility-search">
                  <input class="form-control" id = "search-bar-direct" name = "keyword" type="text" placeholder="Search">
                  <div class = "detailed_search_bar" id = "detailed-search-bar">
                      <div class = "content_bar" data-conditional-count = "0" id = "content_bar">

                      </div> <!-- Content Bar -->
                      <div class="control_bar">
                          <button class="detail_control_bar_button" onclick="clearConditionBar(this)">Clear</button>
                          <button class="detail_control_bar_button" onclick="addConditionBar(this)">+</button>
                          <button class="detail_control_bar_button" onclick="performDetailedSearch(this)">Search</button>
                      </div>
                  </div>
                  <div class="utility-search-button-bar">
                    <button class="utility-option-button" onclick="openDetailedSearchBar(this)" toggle-stat="close">Detailed Search</button>
                    <button class="utility-option-button hideContent" id= "utility-option-button-filter"red  onclick="clearStudyFilter()">Clear Study Filter</button>
                  </div>
              </div>
              <div class="utility-option">
                  <button class="utility-option-button" onClick = "panelToggleLeft(this)">Columns</button>
                  <button class="utility-option-button" onClick = "panelToggleRight(this)">Studies</button>
                  <button class="utility-option-button" onClick="loadGroupBar(this)" toggle='open'>Compare</button>
              </div>
            </div> <!-- Compare Row Header Close -->

                <!-- <div class='col-md-4 col-md-offset-2'>
                        <h3>Select Columns</h3>
                        <div class="panel-group">
                                <div class="panel panel-default">
                                        <div class="panel-heading">
                                                <h4 class="panel-title">
                                                        <a data-toggle="collapse" href="#collapseOne">Columns</a>
                                                </h4>
                                        </div>
                                        <div id="collapseOne" class="panel-collapse collapse in">
                                                <div class="panel-body columns-accordion-body">
                                                        <?php
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
                                                                                                        <?php
                                                                                                                foreach($viewDetail as $viewColumn){
                                                                                                                        if($viewColumn['Field'] == "qcID")
                                                                                                                                continue;

                                                                                                                        #if (in_array($viewColumn['Field'],$defaultColumns))
                                                                                                                        if (in_array($i, $defaultColumns,true)){
                                                                                                                                $checked = "checked";
                                                                                                                        }else{
                                                                                                                                $checked = "";
                                                                                                                        }
                                                                                                                        echo "<li><input type='checkbox' name='column-".($i)."' value='".$viewColumn['Field']."' class='column-name' name='".$viewColumn['Field']."' ".$checked."  onclick='toggle_column(".($i++).")' > ".str_replace("_"," ",$viewColumn['Field'])."</li>";
                                                                                                                }
                                                                                                        ?>
                                                                                                        </ul>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                        </div>
                                                        <?php }?>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div> -->

                <div class="col-md-12" id='staticView'>
                        <table id="samples" class="table table-hover table-bordered draggable sortable">
                                <thead>
                                        <tr>
                                                <th class="text-center" style="width:100px"><a onclick="selectAll(this)" id="selectAllToggle" style="cursor:pointer">Select</a></th>
                                        <?php
                                                $i=0;
                                                foreach($columns as $column){

                                                        if (in_array($i, $defaultColumns,true)){
                                                                $style = "";
                                                        }else{
                                                                $style = "hidden";
                                                        }

                                                        echo "<th data-col-ind='".($i++)."' class='".$style."'>";
                                                                        echo str_replace("_"," ",$column['Field']);
                                                        echo "</th>";
                                                }
                                        ?>
                                        </tr>
                                </thead>
                                <tbody id="sample_table_hold">
                                <?php
                                        if (!empty($samples)){
                                                $index = 0;
                                                foreach($samples as $sample){
                                                        echo "<tr id='sample-row-".$sample['qcID']."' style='color:#000'>";
                                                                echo "<td class='text-center'><div class='designedCheck'>
                                                                                        <input onclick = 'check(this)' type='checkbox' class='sample-agg' name='sample-".($sample['qcID'])."' value='".$sample['qcID']."' sample-name = ".$sample['Sample']." id='samplecheckbox-".$sample['qcID']."'/>
                                                                                        <label for='samplecheckbox-".$sample['qcID']."' id='label-".$sample['qcID']."'>
                                                                                                <span class='fa fa-dot-circle-o' id='labelIcon-".$sample['qcID']."'></span>
                                                                                        </label>
                                                                                    </div>
                                                                            </td>";
                                                                        $i=0;
                                                                        foreach($columns as $column){
                                                                                if(in_array($i, $defaultColumns,true)){
                                                                                        $style = "";
                                                                                }else{
                                                                                        $style = "hidden";
                                                                                }

                                                                                if ($column['Field'] != 'Sample'){
                                                                                        echo "<td style='background-color:white;' data-col-ind='".($i++)."' class='".$style."'>";
                                                                                                if(is_numeric($sample[$column['Field']])){
                                                                                                        if (array_key_exists("percent_".sha1($column['Field']),$flags )){
                                                                                                                if($flags["percent_".sha1($column['Field'])] === "true"){
                                                                                                                        echo number_format(($sample[$column['Field']]*100), $precision). "%";
                                                                                                                }else{
                                                                                                                        echo number_format($sample[$column['Field']], $precision);
                                                                                                                }
                                                                                                        }else{
                                                                                                                echo $sample[$column['Field']];
                                                                                                        }
                                                                                                }else{
                                                                                                        if (isset($keyword)){
                                                                                                                echo highlight_phrase($sample[$column['Field']], $keyword, "<span style='background-color:'>", "</span>");
                                                                                                        }else{
                                                                                                                echo $sample[$column['Field']];
                                                                                                        }
                                                                                                }
                                                                                        echo "</td>";
                                                                                }else{
                                                                                        echo "<td style='background-color:white;' data-col-ind='".($i++)."' class='".$style."'>";
                                                                                                echo "<a onClick = 'showSampleInfo(this)' id ='moveable".($index)."' sampleId = '".$sample['qcID']."' sampleField = '".((isset($keyword))?highlight_phrase($sample[$column['Field']],$keyword,"<span style='background-color:'>", "</span>"):$sample[$column['Field']])."'>" .((isset($keyword))?highlight_phrase($sample[$column['Field']],$keyword,"<span style='background-color:'>", "</span>"):$sample[$column['Field']]) ."</a>";
                                                                                        echo "</td>";
                                                                                }
                                                                        }
                                                        echo "</tr>";
                                                        $index++;
                                                }
                                        }else{
                                                echo "<p>No Samples</p>";
                                        }
                                ?>
                                </tbody>
                        </table>
                </div>
                <div class="boxBottomControl" id="boxBottomControl">
                        <div class="boxLeftControl">
                                <div class="horizontal">
                                        <div class="hold">
                                                <p onclick="addfinale()" id = "addtogroup" class="simpleButton" addtogroup title="Add Samples To Group"><span class="fa fa-upload fa-rotate-180"></span></p>
                                        </div>
                                        <div class="hold" data-count="1" id="addSign">
                                                <p onclick="addGroup()" title="Add A Group"><span class="fa fa-plus"></span></p>
                                        </div>
                                        <div class="hold">
                                                <p onclick="deleteGroup()" title="Delete Active Group"><span class="fa fa-trash"></span></p>
                                        </div>
                                </div>
                        </div>
                        <div class="boxMiddleControl">
                                <div class="wrapperGroup" id="wrapperGroup" data-count = "1">
                                        <div id="capsule-0" class="capsule" group-no="0" onClick="activateGroup(this)">
                                                <div class="capsuletoggle">
                                                        <div class="groupName">
                                                                <input id="groupNameEditable_0" class='form-switch groupNameEditable' value = "Group 1" placeholder="Group Name"/>
                                                        </div>
                                                </div>
                                                <div class="dropPort" id="dropPort_0">

                                                </div>
                                        </div>
                                </div>
                        </div>
                        <div class="boxRightControl">
                            <div class="horizontal">
                                <div class="hold">
                                        <p onclick="clusterGroup()" title="Cluster Group"><span class="fa fa-clone"></span></p>
                                </div>
                                <div class="hold">
                                        <p onclick="submitCompareForm()" class="simpleButton" id="approveGroupButton" title="Appove For Comparing"><span class="fa fa-chevron-right"></span></p>
                                </div>
                            </div>
                        </div>
                </div>
                <?php
                        $attributes = array('id' => 'compareForm');
                        echo form_open('sample/CompareView',$attributes);
                        echo '<div id="compareAppend"></div>';
                        echo form_close();
                ?>
                <!-- Back Drop Template -->
                <div class="backdrop">
                        <div class="head"><p>Overview</p><button class="utility-option-button" onclick="closeDrop(); return false;">Close</button></div>
                        <div id="append"></div>
                </div>
                <div class="SampleDisplayBackDrop" id ="SampleDisplayBackDrop">
                        <div class='head'>
                                <p>Single Sample</p>
                                <button class='utility-option-button' onclick='closeSingleDrop(); return false;'>Close</button>
                        </div>
                        <div class='backdropSingleContent' id='backdropSingleContent'></div>
                </div>

        </div>
</div>
<div class="full-scale-side-panel-right" data-view="closed" id="right-side-panel">
  <div class="column-content" id="right-side-panel-content">
    <div class="studySummary">
      <div class="header">
          <p>All Studies</p>
      </div>
      <div class="content">
        <?php
          foreach ($study as $key => $value) {
            echo '<a onclick = "showStudy('."'".trim($value)."'".')">'.$value.'</a>';
          }
        ?>
      </div>
    </div>

    <div class="studySummary">
      <div class="header">
          <p>Saved Compare Result's</p>
      </div>
      <div class="content">
          <p>Comming Soon</p>
      </div>
    </div>
  </div>
</div>
<div class="full-scale-side-panel" data-view = "closed" id="left-side-panel">
  <div class="column-content">
      <div class="panel-group">
          <div class="panel panel-default">
              <div class="panel-heading">
                  <h4 class="panel-title">
                      <a data-toggle="collapse" href="#collapseOne">Columns</a>
                  </h4>
              </div>
              <div id="collapseOne" class="panel-collapse in">
                <div class="panel-body columns-accordion-body">
                    <?php
                      $i=0;
                      foreach($view as $viewName => $viewDetail){
                    ?>
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
                                      <?php
                                        foreach($viewDetail as $viewColumn){
                                            if($viewColumn['Field'] == "qcID"){
                                                    continue;
                                            }
                                            if (in_array($i, $defaultColumns,true)){
                                                    $checked = "checked";
                                            }else{
                                                    $checked = "";
                                            }
                                            echo "<li><input type='checkbox' name='column-".($i)."' value='".$viewColumn['Field']."' class='column-name' name='".$viewColumn['Field']."' ".$checked."  onclick='toggle_column(".($i++).")' > ".str_replace("_"," ",$viewColumn['Field'])."</li>";
                                        }
                                      ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                      </div>
                    <?php
                      } //End of Foreach
                    ?>
                  </div>
              </div>
          </div>
      </div>
    </div>
</div>
<script type="text/javascript" src="<?=$resources?>js/study.js"></script>
<script type="text/javascript" src="<?=$resources?>js/drag2.0.js"></script>
<script type="text/javascript" src="<?=$resources?>js/demandsearch.js"></script>
<script type="text/javascript" src="<?=$resources?>js/demandsearch-framework.js"></script>
