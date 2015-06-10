jQuery(function ($) {
    $.fn.showHide = function (options) {

        //default vars for the plugin
        var defaults = {
            speed: 1000,
            easing: '',
            changeText: 0,
            showText: 'Show',
            hideText: 'Hide'
            
        };
        var options = $.extend(defaults, options);
        $(this).click(function () {
        	 var $name = event.target.rel.replace('#slidingDiv','');
        	 //var $row_name = '#'.concat(event.target.rel.replace('#slidingDiv',''),'_plt_row')
        	 var $row_name = '[id=' + $name + '_plt_row]';
             var $db_name = this.innerText.split(' ').join('_') //this.innerText.replace(' ','_');
            
            var $j = 1;
            var $my_data = [];
            try{
                for (var i = 0; i < $.parseJSON($("#" + $name + "_plt_data").text()).length; i++) {
                    if($.parseJSON($("#" + $name + "_plt_data").text())[i][$db_name]) {
                        $my_data.push( ({"x": $j, "y": parseFloat($.parseJSON($("#" + $name + "_plt_data").text())[i][$db_name]), "name": $.parseJSON($("#" + $name + "_plt_data").text())[i]['unique_ID']}) );
                        $j++
                    };
                }
            }catch(e){};
 
             var $width = $("#slidingDiv" + $name).parent().width()*(0.96);
        	 thing($row_name,$my_data, $width);
             $('.toggleDiv').slideUp(options.speed, options.easing);    
             // this var stores which button you've clicked
             var toggleClick = $(this);
             // this reads the rel attribute of the button to determine which div id to toggle
             var toggleDiv = $(this).attr('rel');
             // here we toggle show/hide the correct div at the right speed and using which easing effect
             $(toggleDiv).slideToggle(options.speed, options.easing, function() {
             // this only fires once the animation is completed
             if(options.changeText==1){
             $(toggleDiv).is(":visible") ? toggleClick.text(options.hideText) : toggleClick.text(options.showText);
             }
              });
           
          return false;
               
        });

    };
});

$(document).ready(function(){
   $('.show_hide').showHide({        
        speed: 1,  // speed you want the toggle to happen    
        easing: '',  // the animation effect you want. Remove this line if you dont want an effect and if you haven't included jQuery UI
        changeText: 0, // if you dont want the button text to change, set this to 0
        showText: 'View',// the button text to show when a div is closed
        hideText: 'Close', // the button text to show when a div is open     
    }); 


});

function thing(name, data, width) { 
    $(name).highcharts({
        chart: {
            type: 'scatter',
            marginTop: 30,
            width: width,
            height: null
        },
        legend: {
            enabled: false,
        },
        title: {
            text: ""
        },
        xAxis: {
            title: {
                text: 'Sample'
            },
            allowDecimals: false
        },
        yAxis: {
            title: {
                text: 'Value'
            },
            max: null,
        },

        plotOptions: {
            series: {
                point: {
                    events: {
                        click: function () {
                            var chart = this.series.chart;
                            if (!chart.lbl) {
                                chart.lbl = chart.renderer.label('')
                                    .attr({
                                        padding: 10,
                                        r: 10,
                                        fill: Highcharts.getOptions().colors[1],
                                        zIndex: 2
                                        
                                    })
                                    .css({
                                        color: '#FFFFFF'
                                    })
                                    .add();
                            }
                            chart.lbl
                                .show()
                                .attr({ 
                                    text: this.name
                                });
                        }
                    }
                },
                events: {
                    mouseOut: function () {
                        if (this.chart.lbl) {
                            this.chart.lbl.hide();
                        }
                    }
                }
            }
        },

        tooltip: {
            enabled: false
        },
        series: [{
            data: data,
            zIndex: 1
        }]
    });
};
