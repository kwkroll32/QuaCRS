
//var base_url = "http://localhost/qc/"
function get_base_URL () {
    var base_url_temp = location.protocol + "//" + location.hostname + location.pathname;
    var base_index = base_url_temp.indexOf("index.php");
    if(base_index == -1){
    	return base_url_temp;
    }
    else{
    	return base_url_temp.substring(0,base_index);
    }
}
var base_url = get_base_URL();


function redirect_to(url){
	//this is how you should use it
	
	//onclick='redirect_to(\"".$base_url."index.php/sample/detail/".$sample['qcID']."\")'
	window.location= url;
}

function toggle_column(columnID){
	$('#samples tr *[data-col-ind="'+(columnID)+'"]').toggleClass('hidden');
	
	// This is after we have changed the class of the element
	var elementClass = $('#samples tr *[data-col-ind="'+(columnID)+'"]').attr("class");

	if (elementClass.indexOf("hidden") == -1){
		//when you check a column
		$.cookie("col-order-set", 1);
		$.cookie("col-order-"+columnID,parseInt(columnID));
	}
	else{
		//when you uncheck a column
		$.removeCookie("col-order-"+columnID);
		

		//get all the checked checkboxes in the select column seciton:
		//and check to see if the length of it is zero or not
		
		var numberOfCheckedCheckboxes = $("input.column-name:checked").length;
		if(numberOfCheckedCheckboxes == 0){
			//alert("There is nothing thats checked anymore");
			$.removeCookie("col-order-set");
		}
	}
}

function toggle_hidden_columns(tableID){
	$("#"+tableID+" tr *[data-secondary=true]").toggleClass('hidden');
	var btnText = $("#btn_"+tableID).text();

	if (btnText == "Hide Details")
		$("#btn_"+tableID).text("Show Details");
	else
		$("#btn_"+tableID).text("Hide Details");

}

function toggle_table(tableID){
	$("#"+tableID).toggleClass("hidden");

	var btnText = $("#btn_"+tableID).text();

	if (btnText == "Hide")
		$("#btn_"+tableID).text("Show");
	else
		$("#btn_"+tableID).text("Hide");
	
}


/*function generate_report(sampleID){
	//alert(base_url+'index.php/ajax/generate_single_report');
	$.ajax({
		url:base_url+'index.php/ajax/generate_report',
		type:"POST",
		data:{id: sampleID},
		success: function(fileName){
			console.log(fileName);

			if(fileName == "NOT ACCESSIBLE" || fileName.length > 50){
				alert("ERROR: You need to change the permissions on the assets/reports folder and set to 777");
			}
			else{
				window.location = base_url + "assets/reports/"+fileName;	
			}
    	}
	});
}*/

function toggleSelectAll(status){
	if(status == "true"){
		$("input.sample-agg:checkbox").prop('checked', false);
	}
	else{
		$("input.sample-agg:checkbox").prop('checked', true);
	}
}

function search(keyword){
	//alert(keyword);
	$.ajax({
		url:base_url+"index.php/search/pre_search",
		type:"POST",
		data:{"keyword":keyword},
		success:function(msg){
			alert(msg);
		}
	});
}

$( document ).ajaxStart(function() {
	var overlay = new ItpOverlay();
	overlay.show("body");
});

$( document ).ajaxStart(function() {
	var overlay = new ItpOverlay();
	overlay.hide("body");
});


$( document ).ready(function() {
	$.cookie.raw = true;

	$('.collapse').collapse({hide: true});
	$(".fancybox").fancybox();
	
	$("#samples").tablesorter({
		headers:{
			0:{
				sorter:false
			}
		}
	});

	/*$('#download-report').click(function(){
		var sampleID = $(this).attr("data-sample-id");
		generate_report(sampleID);
	});*/

	$("#toggleCheckbox").change(function(){
		//alert("test");
		var status = $(this).attr("data-checked");
		toggleSelectAll(status);
		if (status == "false")
			$(this).attr("data-checked", "true");
		else
			$(this).attr("data-checked", "false");
	});

	/*$("#search-key").click(function(){
		var keyword = document.getElementById("search-bar").value;
		if (keyword == "")
			return;
		search(keyword);
	});*/
});