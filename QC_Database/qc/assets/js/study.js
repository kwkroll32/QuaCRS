/*
  @author Taha Mazher Topiwala
*/

var SEARCHBAR = _("search-bar-direct");

function panelToggleRight(el){
    var panel = _("right-side-panel");
    var mask = _("mask-modal-panel").classList.add("mask-visible");
    panel.style.width = "300px";
    _("staticView").style.paddingRight = "210px";
}


function showStudy(study){
   SEARCHBAR.placeholder = "Search study " + study;
   SEARCHBAR.setAttribute("data-condition", study);
   _("utility-option-button-filter").classList.remove("hideContent");
   performSearchForStudy(study);
}

function clearStudyFilter(){
  SEARCHBAR.placeholder = "Search";
  SEARCHBAR.value = "";
  SEARCHBAR.removeAttribute("data-condition");
  _("utility-option-button-filter").classList.add("hideContent");
	resetSearchResultsToDefault();
}

function performSearchForStudy(study){
  if(study.length > 1){
		var searchterm = "searchterm=" + study.trim();
		var xlmhttp;
		if(window.XMLHttpRequest){
			xmlhttp = new XMLHttpRequest();
		} else{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		xmlhttp.onreadystatechange = function(){
		    if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
	            var data = JSON.parse(xmlhttp.responseText);
							resetSearchResultsToDefault();
		        	displaySampleSearchColumn(data);
	        }
	    };
	    xmlhttp.open("POST",xhr_root+"index.php/search/studySearch",true);
	    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	    xmlhttp.send(searchterm);

	}else{
		resetSearchResultsToDefault();
	}
}
