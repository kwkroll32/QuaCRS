<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<p>
				<h1>Readme</h1>

				<h3>Logging In</h3>
				The default username and password for QuaCRS is:
				<br>
				<b>Username: </b>user
				<br>
				<b>Password: </b>password
				<br><br>

				<h3>Installation Readme</h3>
                                <a href="http://bioserv.mps.ohio-state.edu/QuaCRS/QuaCRS_readme.txt">Latest Installation Readme</a> (Updated 7/11/14)
				<br><br>

				<h3>Site Manual</h3>
				<h4>Sample View</h4>
				This is the home view for the QuaCRS database. It is accessible from any other page by 				clicking the <b>QuaCRS</b> logo at the top left of the page.
				<br><br>

				<t>There are four main aspects to the <b>Sample View</b> page:
				<br>
				<ol>
					<li>The <b>Search Bar</b> – used to search for specific samples. To use, type a query and select <b>Search</b>. To only search a specific column, enter "Column:Term" (ex: "Study:Study1").</li>
					<li>The <b>Select Columns Menu</b> – A dropdown menu used to change which information is visible in the <b>Sample View</b>. Information is organized into descriptive subcatagories. Clicking on any category will open or close it to allow columns to be selected. Checking or unchecking a box shows or hides that information for all samples. Note that some samples may not have data for certain columns, in which case that column will be blank for that sample.</li>
					<li>The <b>Aggregate</b> button. This button is used to generate <b>Aggregate Results</b> for multiple samples (described below).</li>
					<li>The <b>Sample List</b> – Each row in this list is a different sample. The visible columns can be selected by the <b>Select Columns Menu</b>. The checkboxes to the left of each sample are used to select samples for use in <b>Aggregate Reports</b>. Clicking on a sample name will display <b>Sample Details</b> for that sample (described below).  Clicking on the header of a column sorts the samples by that column and columns can be reordered via drag and drop.</li>
				</ol>
				<br>

				<h4>Sample Details</h4>
				This page shows a detailed overview of the QC for a specific sample. QC is organized into descriptive subcatagories. Clicking the <b>Download Report</b> button will download an <b>Individual Report</b> (described below). Clicking the <b>Show/Hide</b> button will show or hide a specific section, allowing other sections to be viewed more easily. The <b>Duplicate Stats</b> and <b>Plots</b> sections can also be shown or hidden by clicking on the label. Clicking on a plot will zoom in on it. In this view, the arrow keys can be used to scroll between plots.
				<br><br>

				<h4>Individual Report</h4>				An <b>Individual Report</b> is a CSV table containing two lines – a header line, and a data line. It contains all of the same QC information as the <b>Sample Details</b> page. Images are not included, but are listed as filename paths from the root data directory.
				<br><br>

				<h4>Aggregate Results</h4>
				The <b>Aggregate Results</b> page is similar to the <b>Sample Details</b> page, except it can describe multiple samples. Most values are outlined as minimum, average and maximum values across the selected samples. The qualitative FastQC values are outlined by the count of samples that passed, failed or received a warning. Clicking the <b>Show/Hide Details</b> button above the FastQC Stats section will provide more or less information on the FastQC results. Clicking the <b>Download Report</b> button will download an <b>Aggregate Report</b> (described below). Unlike the <b>Sample Details</b> page, the <b>Aggregate Results</b> page does not include plots, since the number of plots quickly becomes impractical when viewing many samples.
				<br><br>

				<h4>Aggregate Report</h4>
				An <b>Aggregate Report</b> is identical to an <b>Individual Report</b>, except that it can contain more than two lines. The first line is still the header, and every line after it is a different sample.
				<br><br>

				<h4>Other Pages</h4>
				There are several other pages in the QuaCRS interface which are accessible by clicking the links at the top of any page:
				<br>
				<ol>
					<li>The <b>Downloads</b> page contains links to download QuaCRS, as well as a version history.</li>
					<li>The <b>About Us</b> page contains information about The Ohio State University Comprehensive Cancer Center, as well as contact information for the maintainers of this site.</li>
					<li>The <b>Readme</b> page contains this manual as well as a link to the latest Installation Readme for QuaCRS.</li>
				</ol>
			</p>
		</div>
	</div>
</div>

