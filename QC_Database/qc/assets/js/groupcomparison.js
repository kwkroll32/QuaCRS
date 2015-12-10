/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function _(el){ 
    return document.getElementById(el); 
}

function collapseViewIndividual(el){
    var target = el.getAttribute("target");
    var toggle = el.getAttribute("data-toggle");
    var dataDiv = _(target);
    var height = dataDiv.getAttribute("height");
    switch(toggle){
        case 'close': 
                el.innerHTML = "Open Table";
                dataDiv.style.height = "0px";
                el.setAttribute("data-toggle","open");
                el.style.borderColor = "#EDEDED";
            break;
        case 'open':
                el.innerHTML = "Collapse Table";
                dataDiv.style.height = "auto";
                el.setAttribute("data-toggle","close");
                el.style.borderColor = "green";
            break;
    }
    return false;
}

function expandInt(el){
    var toggle = el.getAttribute("toggle");
    var target = el.getAttribute("target");
    switch(toggle){
        case 'open':
            el.style.color = "#ff004c";
            el.setAttribute("toggle","close");
            _(target).style.height = "auto";
            break;
        case 'close':
            el.style.color = "#007FFF";
            el.setAttribute("toggle","open");
             _(target).style.height = "0px";
            break;
    }
}

$(function() {
    $('a[href*=#]:not([href=#])').click(function() {
      if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
        var target = $(this.hash);
        target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
        if (target.length) {
          $('html,body').animate({
            scrollTop: target.offset().top-200
          }, 1000);
          return false;
        }
      }
    });
});

function showSampleInfo(el){
    var text = _("backDropTitle").innerHTML = "Single Sample";
    var id = el.getAttribute("sample-id");
    $("#backdrop").fadeIn(500);
    var xmlhttp;
    if (window.XMLHttpRequest)
      {
      xmlhttp=new XMLHttpRequest();
      }
    else
      {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange=function()
      {
      if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            _("backdropContent").innerHTML = xmlhttp.responseText;
        }
      };
    xmlhttp.open("POST","singleView",true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send("sampleid="+id);
}

function closeDrop(){
    $("#backdrop").fadeOut(500);
}


function toggleDataView(el){
    var container = _("bottomHalfContent");
    var containerChildren = container.children;
    var togglebar = _("togglebar");
    var togglebarChildren = togglebar.children;
    var toggleValue = el.getAttribute("data-toggle");
    var i = null;
    for (i = 0 ; i < containerChildren.length ; i++){
        containerChildren[i].classList.remove('showContent');
        containerChildren[i].classList.add('hideContent');
        togglebarChildren[i].style.color = "#000";
        togglebarChildren[i].style.borderColor = "#CCC";
    }
    
    switch (toggleValue) {
        case '1' :  
            containerChildren[0].classList.add("showContent");
            break;
        
        case '2' :
            containerChildren[1].classList.add("showContent");
            break;
        
        default:
            containerChildren[0].classList.add("showContent");
        break;
    }

    el.style.color = "#0Cf";
    el.style.borderColor = "#0Cf";
}