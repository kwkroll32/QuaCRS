<?php
$base_url = $this->config->item('base_url');
$resources = $this->config->item('resources');
?>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <style>
                b{
                    color : #2238e6
                }
                
                p{
                    color : #333333;
                }
                
                .site-info-hold{
                    width: 100%;
                    margin: 0px auto;
                    min-height: 50px;
                    padding-bottom: 20px;
                }
                
                .site-info-hold[cred]{
                    width: 90%;
                    margin-top: 30px;
                    border:3px solid #FF6666;
                    border-radius: 10px;
                    text-align: center;
                }
                
                .site-info-hold[sitemap]{
                    width: 100%;
                    margin-top: 30px;
                    margin-bottom: 10px;
                    border-bottom:2px solid #666666;
                }
                
                .site-info-hold[cred] h3{
                    font-size: 20px;
                    letter-spacing: 1px;
                    color:#000;
                }
                
                .site-info-hold[sitemap] h4{
                    font-size: 20px;
                    letter-spacing: 1px;
                    color:#000;
                    margin-bottom: 30px;
                }
                
                .site-info-hold[sitemap] ul p{
                    font-size: 20px;
                }

                .multipleView{
                    width: 100%;
                    height: auto;
                    margin-bottom: 10px;
                    border-bottom: 1px solid #EDEDED;
                }
                
                .discriptiveimage{
                    width: 100%;
                    margin: 0px auto;
                    min-height: 200px;
                    border-bottom:1px solid #EDEDED;
                    margin-bottom: 10px;
                }
                
                .discriptiveimage img{
                    width: 100%;
                }
                
                .collapsingTable{
                    margin-top: 20px;
                    cursor: pointer;
                    height: 500px;
                    overflow: hidden;
                    -webkit-transition: height 1s ease;
                    -moz-transition: height 1s ease;
                      -o-transition: height 1s ease;
                     -ms-transition: height 1s ease;
                         transition: height 1s ease;
                }
                
                .buttonholdread{
                    width: 100%;
                    height: auto;
                    margin-bottom: 20px;
                    
                    padding-left: 20px;
                }
                
                .buttonholdread span{
                    font-size: 20px;
                }
            </style>
            
            <h1 class='text-center'>Readme</h1>
            <div class='site-info-hold' cred>
                <h3>Logging In</h3>
                <p>The default username and password for QuaCRS is:</p>
                <p><b>Username: </b>user<p>
                <p><b>Password: </b>password</p>
                <h3>Installation Readme</h3>
                <a href="http://bioserv.mps.ohio-state.edu/QuaCRS/QuaCRS_readme.txt">Latest Installation Readme</a> (Updated 7/11/14)
            </div>
            
            <h3  class='text-center'>Site Manual</h3>
            <div class='site-info-hold' sitemap>
                <h4>Sample View</h4>
                <p>This is the home view for the QuaCRS database. It is accessible from any other page by 
                clicking the <b>QuaCRS</b> logo at the top left of the page.</p>
                
                <p>There are four main aspects to the <b>Sample View</b> page:</p>
                <ol>
                    <li><p>The <b>Search Bar</b> – used to search for specific samples. To use, type a query and select <b>Search</b>. To only search a specific column, enter "Column:Term" (ex: "Study:Study1").</p></li>
                    <li><p>The <b>Select Columns Menu</b> – A dropdown menu used to change which information is visible in the <b>Sample View</b>. Information is organized into descriptive subcatagories. Clicking on any category will open or close it to allow columns to be selected. Checking or unchecking a box shows or hides that information for all samples. Note that some samples may not have data for certain columns, in which case that column will be blank for that sample.</p></li>
                    <li><p>The <b>Compare</b> button. This button is used to initiate the group control bar for multiple groups with samples (described below).</p></li>
                    <li><p>The <b>Sample List</b> – Each row in this list is a different sample. The visible columns can be selected by the <b>Select Columns Menu</b>. The checkboxes to the left of each sample are used to select samples for adding to the <b>Active Group</b>. Clicking on a sample name will display <b>Sample Details</b> for that sample (described below).  Clicking on the header of a column sorts the samples by that column and columns can be reordered via drag and drop.</p></li>
                </ol>
            </div>
            <div class='site-info-hold' sitemap>
                <h4>Sample Details</h4>
                <p>This page shows a detailed overview of the QC for a specific sample. QC is organized into descriptive subcatagories. Clicking the <b>Download Report</b> button will download an <b>Individual Report</b> (described below). Clicking the <b>Show/Hide</b> button will show or hide a specific section, allowing other sections to be viewed more easily. The <b>Duplicate Stats</b> and <b>Plots</b> sections can also be shown or hidden by clicking on the label. Clicking on a plot will zoom in on it. In this view, the arrow keys can be used to scroll between plots.</p>
            </div>
            <div class='site-info-hold' sitemap>
                <h4>Individual Report</h4>
                <p>An <b>Individual Report</b> is a CSV table containing two lines – a header line, and a data line. It contains all of the same QC information as the <b>Sample Details</b> page. Images are not included, but are listed as filename paths from the root data directory.</p>
            </div>
            <div class='site-info-hold' sitemap>
                <h4>Compare Bar</h4>
                <p>On clicking the <b>Compare</b> button on the sample page will initiate the compare bar from the bottom of the screen. <b>Like:</b></p>
                <div class='discriptiveimage' single>
                    <img src = '<?=$resources?>/images/11.png'/>
                </div>
                <p>As visible there are <b>3 control buttons and one input field</b> on the left hand side and on the right hand side <b>3 control buttons</b></p>
                <h4>Left hand side buttons</h4>
                <div class='buttonholdread'>
                    <p><span class="fa fa-download"></span> : <b>Add Sample Button</b> used to add selected samples to the active group </p>
                    <p><span class="fa fa-plus"></span> : <b>Addition Button</b> used to add a group.</p>
                    <p><span class="fa fa-trash"></span> : <b>Delete Button</b> used to delete a group. </p>
                </div>
                <h4>Right hand side buttons</h4>
                <div class='buttonholdread'>
                    <p><span class="fa fa-compress"></span> : <b>Cluster Button</b> used to view all groups in a cumulative way.</p>
                    <p><span class="fa fa-check"></span> : <b>Approve Button</b> used to approve the created groups to allow comparison.</p>
                    <p><span class="fa fa-chevron-right"></span> : <b>Compare Button</b> used to compare the groups. But before that the user must click the approve button on top.</p>
                </div>
            </div>
            <div class='site-info-hold' sitemap>
                <h4>Compare Details Page</h4>
                <p>The <b>Compare Results</b> which allows the user to make individuals groups. Each group can hold multiple samples within it with a custom name or a default name (Eg. Group 1) and an individual color. Most values are outlined as minimum, average and maximum values across the selected samples. The qualitative FastQC values are outlined by the count of samples in each group that passed, failed or received a warning. Clicking the <b>Show/Hide Details</b> button above the FastQC Stats section will provide more or less information on the FastQC results. Clicking the <b>Download Report</b> button will download an <b>Compare Report</b> (described below).</p>
                <h4>View's within compare page</h4>
                <div class="multipleView">
                    <h4>Data Table (View)</h4>
                    <div class='collapsingTable' toggle='open' onclick='openBox(this);'>
                        <ul>
                            <li><p>FastQC Stat</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/1.png'/>
                                </div>
                            </li>
                            <li><p>Alignment Stats</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/2.png'/>
                                </div>
                            </li>
                            <li><p>Duplication</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/3.png'/>
                                </div>
                            </li>
                            <li><p>Expression</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/4.png'/>
                                </div>
                            </li>
                            <li><p>GC Content</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/5.png'/>
                                </div>
                            </li>
                            <li><p>Genomic Stats</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/6.png'/>
                                </div>
                            </li>
                            <li><p>Library Stats</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/7.png'/>
                                </div>
                            </li>
                            <li><p>Splicing</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/8.png'/>
                                </div>
                            </li>
                            <li><p>Strand Stats</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/9.png'/>
                                </div>
                            </li>
                            <li><p>Variants</p>
                                <div class='discriptiveimage'>
                                    <img src = '<?=$resources?>/images/10.png'/>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="multipleView">
                    <h4>Graph (View)</h4>
                    <p>This view allows to plot a graph based on an selected <b>X Axis</b> and <b>Y Axis</b></p>
                </div>
            </div>
            <div class='site-info-hold' sitemap>
                <h4>Compare Report</h4>
                <p>An <b>Compare Report</b> is identical to an <b>Individual Report</b>, except that it can contain more than two lines. The first line is still the header, and every line after it is a different sample.</p>
            </div>
            
            <div class='site-info-hold' sitemap>
                <h4>Other Pages</h4>
                <p>There are several other pages in the QuaCRS interface which are accessible by clicking the links at the top of any page:</p>
                <ol>
                    <li>The <b>Downloads</b> page contains links to download QuaCRS, as well as a version history.</li>
                    <li>The <b>About Us</b> page contains information about The Ohio State University Comprehensive Cancer Center, as well as contact information for the maintainers of this site.</li>
                    <li>The <b>Readme</b> page contains this manual as well as a link to the latest Installation Readme for QuaCRS.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<script>
    function openBox(el){
        var toggle = el.getAttribute("toggle");
        switch(toggle){
            case'open':
                el.style.height = "2550px";
                el.setAttribute("toggle","close");
                break;
            case 'close':
                el.style.height = "500px";
                el.setAttribute("toggle","open");
                break;
        }
    }
</script>

