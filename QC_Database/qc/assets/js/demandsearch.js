/*
	@author Taha Mazher Topiwala
*/
var SelectAllToggle = _("selectAllToggle");
var search_box = document.getElementById('search-bar-direct'); // Search box

var headerTimer = setInterval(function(){	updateTitle(); }, 1500);

function updateTitle(){
	if(Object.keys(Master_Search_Resulting_array).length > 0){
		SelectAllToggle.innerHTML = "Select All";
	}
}

function emptySearchField(){
	resetSearchResultsToDefault();
	_("search-bar").value = "";
}

function displaySampleSearchColumn(data){
	if(data.length > 0){
		var sampleTable = _("sample_table_hold").children;
		for (var i = sampleTable.length - 1; i >= 0; i--) {
			var child = sampleTable[i];
			var idOfSample = child.id.split("-");
			if(data.indexOf(idOfSample[2]) == -1){
				child.style.display = "none";
			}
		}

		Master_Search_Resulting_array = data; // Setting the Master array declared in the primary sample file with qcID that have been recieved.
	}else{
		var sampleTable = _("sample_table_hold").children;
		for (var i = sampleTable.length - 1; i >= 0; i--) {
			sampleTable[i].style.display = "none";
		}
		Master_Search_Resulting_array = {};
	}
}

function resetSearchResultsToDefault(){
	var sampleTable = _("sample_table_hold").children;
	for (var i = sampleTable.length - 1; i >= 0; i--) {
		sampleTable[i].style.display = null;
	}
	Master_Search_Resulting_array = {};
	SelectAllToggle.innerHTML = "Select";
	SelectAllToggle.setAttribute('onClick','selectAll(this)');
}

function selectAll(el){
	if(Object.keys(Master_Search_Resulting_array).length > 0){
		for(var i = 0; i < Master_Search_Resulting_array.length; i++){
			var sampleid = Master_Search_Resulting_array[i];
			checkFromResultingArray(_("samplecheckbox-"+sampleid));
		}
	}
	el.setAttribute('onClick','deselectAll(this)');
	if(Object.keys(Master_Search_Resulting_array).length !== 0){
		SelectAllToggle.innerHTML = "Deselect All";
	}
	clearInterval(headerTimer);
}

function deselectAll(el){
	if(Object.keys(Master_Search_Resulting_array).length > 0){
		for(var i = 0; i < Master_Search_Resulting_array.length; i++){
			var sampleid = Master_Search_Resulting_array[i];
			uncheckFromResultingArray(_("samplecheckbox-"+sampleid));
		}
	}
	el.setAttribute('onClick','selectAll(this)');
	if(Object.keys(Master_Search_Resulting_array).length !== 0){
		SelectAllToggle.innerHTML = "Select All";
	}
}

// Search function

function detailedSearch(value) {
	if(value.length == 0){
		resetSearchResultsToDefault();
	}else{
		var json_string = "searchterm=" + JSON.stringify(value);
		var xmlhttp;
		if (window.XMLHttpRequest){
	    	xmlhttp = new XMLHttpRequest();
	    }else{
	        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	    }
	    xmlhttp.onreadystatechange = function(){
		    if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
	            var data = JSON.parse(xmlhttp.responseText);
							resetSearchResultsToDefault();
		        	displaySampleSearchColumn(data);
	        }
	    };
			if(search_box.getAttribute("data-condition") === null){
				xmlhttp.open("POST",xhr_root+"index.php/search/demandsearch",true);
		    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		    xmlhttp.send(json_string);
			}else{
				xmlhttp.open("POST",xhr_root+"index.php/search/demandSearchWithCondition",true);
				xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
				xmlhttp.send(json_string + "&&study="+search_box.getAttribute("data-condition"));
			}

	}
}

function demandsearchSingleTerm(){
	var el = search_box.value;
	if(el.length > 3){
		var searchterm = "searchterm=" + el.trim();
		var xlmhttp;
		if(window.XMLHttpRequest){
			xmlhttp = new XMLHttpRequest();
		} else{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function(){
		    if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
	            var data = JSON.parse(xmlhttp.responseText);
							if(search_box.getAttribute("data-condition") === null){
								resetSearchResultsToDefault();
							}
		        	displaySampleSearchColumn(data);
	        }
	    };
			if(search_box.getAttribute("data-condition") === null){
					xmlhttp.open("POST",xhr_root+"index.php/search/singleSearch",true);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					xmlhttp.send(searchterm);
			}else{
					xmlhttp.open("POST",xhr_root+"index.php/search/singleSearchWithCondition",true);
					xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
					xmlhttp.send(searchterm + "&&study="+search_box.getAttribute("data-condition"));
			}

	}else{
		if(search_box.getAttribute("data-condition") === null){
			resetSearchResultsToDefault();
		}else{
			showStudy(search_box.getAttribute("data-condition"));
		}
	}
}

// Search Timer

var timeInterval = 500; // How long to pause before search
var timer;

search_box.addEventListener('keyup', function() { // Add keyup for the search box
  window.clearTimeout(timer); // Clear timer if the user presses a key
  timer = setTimeout(demandsearchSingleTerm, timeInterval); // Add timer
});

search_box.addEventListener('keydown', function() { // Add keydown event for the search box
  window.clearTimeout(timer); // Clear timer if user presses down on a key
});
