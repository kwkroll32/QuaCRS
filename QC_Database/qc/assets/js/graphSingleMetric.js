/*
	@author Taha Mazher Topiwala
*/

function openGraphRow(el){
    var viewName = el.getAttribute("data-view");
    var displayOption = el.getAttribute("data-toggle");
    var graphingRowElement = _("graphingRow_"+viewName);
    switch (displayOption) {
        case 'open':
            var graphRow = graphingRowElement.style.height = "410px";
            el.setAttribute("data-toggle","close");
            performSingleMetricGraph(graphingRowElement);
            break;
        case 'close':
            el.setAttribute("data-toggle","open");
            graphingRowElement.style.height = "0px";
            break;
        default :
            break;
    }
}

function fireUpGraph(viewName){
  var graphingRowElement = _("graphingRow_"+viewName);
  performSingleMetricGraph(graphingRowElement);
}

function performSingleMetricGraph(graphingRowElement){
    
    var viewName = graphingRowElement.getAttribute("data-view");
    var subtitle = graphingRowElement.getAttribute("data-title");
    var xAxis = JSON.parse(graphingRowElement.getAttribute("data-x"));
    
    var xAxisInt = [];
    
    for (var i = 0; i < xAxis.length; i++) {
    	var eachGroupArray = [];
    	for(var j = 0; j < xAxis[i].length; j++){
    		if(xAxis[i][j] == null){
    			eachGroupArray.push(parseFloat(0));
    		}else{
    			eachGroupArray.push(parseFloat(xAxis[i][j]));
    		}
    	}
    	xAxisInt.push(eachGroupArray);
    }

   	var chart = {
   		type : 'column'
   	}

   	var title = {
   		text : 'Bar Chart'
   	}

   	var xAxis = {
   		categories : MasterSingleGraphSampleNames
   	}

   	var yAxis = {
        min: 0,
        title: {
            text: 'Metric of Interest'
        },
        stackLabels: {
            enabled: true,
            style: {
                fontWeight: 'bold',
            }
        }
    }
   
    var legend = {
        align: 'right',
        x: -30,
        verticalAlign: 'top',
        y: 10,
        floating: true,
        backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
    }

    var tooltip = {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}'
    }

   	var plotOptions = {
   		column : {
   			stacking : 'normal',
   			dataLables : {
   				enabled : false,
   				style : {
                    textShadow: '0 0 3px black'
   				}
   			}
   		}
   	}

   	/* Building Array Dynamically */
   	var series = [];

    var masterXIndexCount = 0;
   	var filledFirstGroup = false;
   	var updatedPoistionCounter = false;
   	var updatedArrayToPosition = false;
   	
   	for (var i = 0 ; i < xAxisInt.length; i++){
   		
   		var arrayForEachGroup = {};

   		var manupilativeArray = xAxisInt[i];
   		var finalFillArray = [];
   		var positionFillCounter = 0;
   		for(var j = 0; j < manupilativeArray.length; j++){
   			var value = manupilativeArray[j];
   			if(!filledFirstGroup){
   			
   				finalFillArray[positionFillCounter] = value;
   			
   			}else if(!updatedArrayToPosition && filledFirstGroup){
   				
   				for(var k = 0; k < masterXIndexCount; k++){
   					finalFillArray[k] = 0;
   				}
   				positionFillCounter = k;
   				finalFillArray[positionFillCounter] = value;
   				updatedArrayToPosition = true;
   			
   			}else if(updatedArrayToPosition){
   				finalFillArray[positionFillCounter] = value;
   			}
   			positionFillCounter++;
   			masterXIndexCount++;
   		}

   		filledFirstGroup = true;
   		updatedArrayToPosition = false;

   		arrayForEachGroup["name"] = MasterGroupNames[i];
   		arrayForEachGroup["color"] = MasterColor[i];
   		arrayForEachGroup["data"] = finalFillArray;

      // Push Into Primary Array
   		
   		series.push(arrayForEachGroup);
  	}

    var targetBlock = "#graphPresentBlock_"+viewName;

    var chartBuild = {};

    chartBuild.chart = chart;
    chartBuild.title = title;
    chartBuild.xAxis = xAxis;
    chartBuild.yAxis = yAxis;
    chartBuild.legend = legend;
    chartBuild.tooltip = tooltip;
    chartBuild.plotOptions = plotOptions;
    chartBuild.series = series;

    $(targetBlock).highcharts(chartBuild);
}

// Plotting For Any Metirc v/s Any Metric

function checkForArrayWithNullValuesAndRepair(arraytocheck){
  for(var i = 0; i < arraytocheck.length; i++){
    for(var k = 0; k < arraytocheck[i].length; k++){
      if(arraytocheck[i][k] == null || arraytocheck[i][k] == 0){
        arraytocheck[i][k] = parseFloat(0);
      }else{
        arraytocheck[i][k] = parseFloat(arraytocheck[i][k]);
      }
    }
  }
  console.log(arraytocheck);
  return arraytocheck;
}

var axis = {"xAxis" : null , "yAxis" : null};
var axisValue = {"xAxis" : null, "yAxis" : null};

function xAxis(value){
    var metric = value.options[value.selectedIndex].value;
    if(metric != "0"){
      axis["xAxis"]  = metric;
      var data = value.options[value.selectedIndex].getAttribute("data-hold");
      axisValue['xAxis'] = JSON.parse(data);
      plotCustomGraphs(); 
    }else{
      axis["xAxis"]  = null;
      axisValue["xAxis"]  = null;
      _("xAxisOptionDropDown").style.borderColor = "red";
      setTimeout(function(){
        _("xAxisOptionDropDown").style.borderColor = null;
      },2000)
    }
}

function yAxis(value){
    var metric = value.options[value.selectedIndex].value;
    if(metric != "0"){
      axis["yAxis"]  = metric;
      var data = value.options[value.selectedIndex].getAttribute("data-hold");
      axisValue['yAxis'] = JSON.parse(data);
      plotCustomGraphs();
    }else{
      axis["yAxis"]  = null; 
      axisValue["yAxis"]  = null;
      _("yAxisOptionDropDown").style.borderColor = "red";
      setTimeout(function(){
        _("yAxisOptionDropDown").style.borderColor = null;
      },2000)
    }
}

function plotCustomGraphs(){
  var xAxisName = axis["xAxis"];
  var yAxisName = axis["yAxis"];
  var xAxisValue = axisValue["xAxis"];
  var yAxisValue = axisValue["yAxis"];
  if((xAxisName && yAxisName) !== null && (xAxisValue && yAxisValue) !== null ){
    xAxisValue = checkForArrayWithNullValuesAndRepair(xAxisValue);
    yAxisValue = checkForArrayWithNullValuesAndRepair(yAxisValue);
    
    presentGraphWithValues(xAxisValue,yAxisValue, xAxisName, yAxisName);
  
  }else{
    if((xAxisValue && xAxisName) == null){
      _("xAxisOptionDropDown").style.borderColor = "red";
      setTimeout(function(){
        _("xAxisOptionDropDown").style.borderColor = null;
      },2000)
    }
    if((yAxisValue && yAxisName) == null){
      _("yAxisOptionDropDown").style.borderColor = "red";
      setTimeout(function(){
        _("yAxisOptionDropDown").style.borderColor = null;
      },2000)
    }
  }
}

function presentGraphWithValues(xAxisValue, yAxisValue, xAxisName, yAxisName){
  
  // Chart Type
  var chart = {
    type : 'scatter',
    zoom : 'xy'
  }

  var title = {
    text : xAxisName + ' vs ' + yAxisName
  }

  var xAxis= {
      title: {
          enabled: true,
          text: xAxisName
      },
      startOnTick: true,
      endOnTick: true,
      showLastLabel: true,
      allDecimals : true
  }

  var yAxis= {
      title: {
          text: yAxisName
      },
      allDecimals : true
  }

  var plotOptions= {
    scatter: {
      marker: {
        radius: 5,
        states: {
          hover: {
            enabled: true,
            lineColor: 'rgb(100,100,100)'
          }
        }
      },  
      states: {
        hover: {
          marker: {
            enabled: false
          }
        }
      }
    }
  } 

  var masterSeries = [];

  for(var i = 0; i < xAxisValue.length; i++){
    var dataArray = {};
    var allPairedValues = [];
    for(var k = 0; k < xAxisValue[i].length; k++){
      var pairedArray = [];
      var xPairValue = xAxisValue[i][k];
      var yPairValue = yAxisValue[i][k];

      if(!(typeof xPairValue == 'undefined') && !(typeof yPairValue == 'undefined')){
        // First add X point
        if(typeof xPairValue == 'undefined'){
          pairedArray[0] = 0;
        }else{
          pairedArray[0] = xPairValue;
        }
        // Second add Y point
        if(typeof yPairValue == 'undefined'){
          pairedArray[1] = 0;
        }else{
          pairedArray[1] = yPairValue;
        }
      }
      // Third add both points to Main Data Array
      allPairedValues.push(pairedArray);
    }
    dataArray["name"] = MasterGroupNames[i];
    dataArray["color"] = MasterColor[i];
    dataArray["data"] = allPairedValues;
    masterSeries.push(dataArray);
  }

  console.log(masterSeries);

  var chartBuild = {};

  chartBuild.chart = chart;
  chartBuild.title = title;
  chartBuild.xAxis = xAxis;
  chartBuild.yAxis = yAxis;
  chartBuild.plotOptions = plotOptions;
  chartBuild.series = masterSeries;

  $("#multipleGraphViewContainer").highcharts(chartBuild);
}