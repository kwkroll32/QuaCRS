$(window).ready( function(){

$(".border-right").each( function(){
	if($(this).attr("toggle") == "close") $(this).click();
});

$(".tableRow").find(".tableColumn").find("p").each( function(){
	var pval = parseFloat( $(this).html().split(" = ")[1] );
	if( pval <= 0.05 )
		$(this).parent().parent().find(".border-right").click();
});

});
