/*
 * @author Taha Mazher Topiwala
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

/* The Following Three Functions Maintain The Toggle For the Back Drop */

function showSampleInfo(el){
    var id = el.getAttribute("sample-id");
    $("#SampleDisplayBackDrop").fadeIn(500);
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
            _("backdropSingleContent").innerHTML = xmlhttp.responseText;
        }
      };
    xmlhttp.open("POST","singleView",true);
    xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xmlhttp.send("sampleid="+id);
}

function closeDrop(){
    $("#SampleDisplayBackDrop").fadeOut(500);
}

function switchViewsBackDrop(el){
  var status = el.getAttribute("data-status");
  var id = el.id;

  var container = _("toggleInfoHold");
  var containerChildren = container.children;
  var togglebar = _("tabBar");
  var togglebarChildren = togglebar.children;
  var toggleValue = el.getAttribute("data-toggle");
  var i = null;
  for (i = 0 ; i < containerChildren.length ; i++){
    containerChildren[i].classList.remove('showContent');
    containerChildren[i].classList.add('hideContent');
    togglebarChildren[i].style.backgroundColor = "white";
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
  el.style.backgroundColor = "#DDD";
}


/* Toggle For Primary Two Views On Compare Page */


function toggleDataView(el){
    $("#floating_jump").toggle(500);

    var container = _("bottomHalfContent");
    var containerChildren = container.children;
    var togglebar = _("togglebar");
    var togglebarChildren = togglebar.children;
    var toggleValue = el.getAttribute("data-toggle");
    var i;
    for (i = 0 ; i < containerChildren.length ; i++){
        containerChildren[i].classList.remove('showContent');
        containerChildren[i].classList.add('hideContent');
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
    el.style.borderColor = "#0Cf";
}
