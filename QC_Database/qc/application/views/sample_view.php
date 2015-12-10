<?php
    $base_url = $this->config->item('base_url');
    $precision = $this->config->item('precision');
    $resources = $this->config->item('resources');
    $xhr_root = $this->config->item('xhr_root');
?>
<link href="<?php echo $resources;?>css/groupstyle.css" rel="stylesheet">
<script type="text/javascript">
    var xhr_root = "<?=$xhr_root?>";
    var Master_Search_Resulting_array = {};
</script>
<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>Search Bar</h3>
            <div class="input-group">
                <input class="form-control" id="search-bar" name="keyword" type="text" placeholder="Search a sample" onkeyup="demandsearch(this)">
                <div class="input-group-btn">
                    <button type="submit" name="submit" onclick="emptySearchField()" class="btn btn-default" tabindex="-1">Clear</button>
                </div>
            </div><!-- /.input-group -->
        </div>
        <div class='col-md-4 col-md-offset-2'>
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
        </div>
        <div class="row compareRowHeader">
            <a class="btn btn-primary" onClick="loadGroup(this)" toggle='open'>Open Compare Bar</a>
            <h3>Sample View</h3>
        </div>
        <div class="col-md-12" id='staticView'>
            <table id="samples" class="table table-hover table-bordered draggable sortable">
                <thead>
                    <tr>
                        <th style="width:50px" class="text-center"><a onclick="selectAll(this)">Select</a></th>
                    <?php
                        $i=0;
                        foreach($columns as $column){
                            if (in_array($i, $defaultColumns,true)){
                                $style = "";
                            }
                            else
                                $style = "hidden";

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
                                echo "<td class='text-center'><div class='designedCheck'><input onclick = 'check(this)' type='checkbox' class='sample-agg' name='sample-".($sample['qcID'])."' value='".$sample['qcID']."' sample-name = ".$sample['Sample']." id='samplecheckbox-".$sample['qcID']."' />
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
                                            echo "<td data-col-ind='".($i++)."' class='".$style."'>";
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
                                            echo "<td data-col-ind='".($i++)."' class='".$style."'>";
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
                    <div id="capsule-0" class="capsule" group-no="0" onClick="activateGroup(this)" activeDefault>
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
                        <p onclick="clusterGroup()" title="Cluster Group"><span class="fa fa-compress"></span></p>
                    </div>
                    <div class="hold">
                        <p onclick="passon();" class="simpleButton" id="approveGroupButton" title="Appove For Comparing"><span class="fa fa-check"></span></p>
                    </div>
                    <div class="hold">
                        <?php
                            $attributes = array('class' => 'compareForm', 'id' => 'compareForm', 'onsubmit' => 'return updateCompareForm();');
                            echo form_open('sample/CompareView',$attributes);
                        ?>
                                <div id="compareAppend"></div>
                                <button type="submit" class="simpleButton" id="passGroup" title="Submit For Comparison" onclick="submit()" ><span class="fa fa-chevron-right"></span></button>
                        <?php
                            echo form_close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Back Drop Template -->
        <div class="backdrop">
            <div class="head"><p>Cluster View</p><button class="btn btn-primary" onclick="closeDrop(); return false;">Close</button></div>
            <div id="append"></div>
        </div>
        <div class="SampleDisplayBackDrop" id ="SampleDisplayBackDrop">
            <div class='head'>
                <p>Single Sample</p>
                <button class='btn btn-primary' onclick='closeSingleDrop(); return false;'>Close</button>
            </div>
            <div class='backdropSingleContent' id='backdropSingleContent'></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo $resources;?>js/drag2.0.js"></script>
<script type="text/javascript" src="<?=$resources?>js/demandsearch.js"></script>