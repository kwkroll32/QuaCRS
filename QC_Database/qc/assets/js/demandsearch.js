function emptySearchField(){
	resetSearchResultsToDefault();
	_("search-bar").value = "";
}

function demandsearch (el) {
	var searchterm = el.value;
	if(searchterm == ""){
		resetSearchResultsToDefault();
	}else{
		var xmlhttp;
		if (window.XMLHttpRequest){
	    	xmlhttp = new XMLHttpRequest();
	    }else{
	        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	    }
	    xmlhttp.onreadystatechange = function(){
		    if (xmlhttp.readyState==4 && xmlhttp.status==200){
	            var data = JSON.parse(xmlhttp.responseText);
	            resetSearchResultsToDefault();
		        displaySampleSearchColumn(data);
	        }
	    };
	    xmlhttp.open("POST",xhr_root+"index.php/search/demandsearch",true);
	    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	    xmlhttp.send("searchterm="+searchterm);
	}
}

function displaySampleSearchColumn(data){
	var sampleTable = _("sample_table_hold").children;
	for (var i = sampleTable.length - 1; i >= 0; i--) {
		var child = sampleTable[i];
		// Split array to get value sample id
		var idOfSample = child.id.split("-");
		if(data.indexOf(idOfSample[2]) == -1){
			child.style.display = "none";
		}
	}
	Master_Search_Resulting_array = data;
}

function resetSearchResultsToDefault(){
	var sampleTable = _("sample_table_hold").children;
	for (var i = sampleTable.length - 1; i >= 0; i--) {
		var child = sampleTable[i];
		child.style.display = null;
	}
	Master_Search_Resulting_array = {};
}

function selectAll(el){
	if(Master_Search_Resulting_array.length > 0){
		for(var i = 0; i < Master_Search_Resulting_array.length; i++){
			var sampleid = Master_Search_Resulting_array[i];
			checkFromResultingArray(_("samplecheckbox-"+sampleid));
		}
	}
}