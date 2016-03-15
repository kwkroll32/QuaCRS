/*
  This controls the "Jump To" floating menu on the comparison page.

  @author Logan Walker
*/

$(window).ready(function(){

var jump_style = "min-width:200px; background-color: white; \
padding:10px; font-size:17px; \
position:fixed; left:30px; bottom:-2px; \
border: 2px solid #CCC; \
-moz-border-radius-topleft: 4px; \
-webkit-border-top-left-radius: 4px; \
 border-top-left-radius: 4px; \
-moz-border-radius-topright: 4px; \
-webkit-border-top-right-radius: 4px; \
border-top-right-radius: 4px;";

$(body).append("<div id='floating_jump' style='" + jump_style + "'></div>");
var jp = $("#floating_jump");

var toAdd = "<center id='float_center'>" +
    "<span style='font-size:20px'>" +
    $($(".linkHold")[0]).parent().find(".topBanner").text() +
    "</span><div style='width:100%; height:4px;'></div>";

$(".linkHold").each( function( i, e ){
	toAdd += "<div style='padding-top:1px;'>" + $(this).html() + "</div>";
});

var jump_hidden = false;

$(jp).append( toAdd + "</center>" );
$(jp).click(function(event){
  var hide_height = $("#floating_jump").height() - 20;

	if(!jump_hidden) {
		$("#floating_jump").animate({
			bottom: "-" + hide_height + "px"
		}, 400, function() { jump_hidden = true; });
	} else {
		$("#floating_jump").animate({
			bottom: "-2px"
		}, 400, function() { jump_hidden = false; });
	}
});

$(jp).click();

});
