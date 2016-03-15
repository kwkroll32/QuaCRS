/*
  This controls the "Jump To" floating menu on the comparison page.

  @author Logan Walker
*/

$(window).ready(function(){

var jump_style = "width:200px; background-color: white; \
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

$(jp).append( toAdd + "</center>" );

var scrolled = true;
$(window).scroll(function(event){ scrolled = true; });

function toggleJump() {
	if( !scrolled ) return;
	var hide_height = $("#floating_jump").height() + 50;

	if($("#menu")[0].getBoundingClientRect().top * -1  < $("#menu").height()) {
		$("#floating_jump").animate({
			bottom: "-" + hide_height + "px"
		}, 400, function() {});
	} else {
		$("#floating_jump").animate({
			bottom: "-2px"
		}, 400, function() {});
	}

	scrolled = false;
}

toggleJump();
setInterval( toggleJump, 150);

});
