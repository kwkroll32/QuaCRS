/*
 * @author Taha Mazher Topiwala
 */


function _(el){
    return document.getElementById(el);
}

var array_group = ["0"];
var array_sample  = new Array ();
var array_color = ["#FD6E01","#DC01FD","#E4FD01","#0145FD","#FD014D","#FD0101","#01FDFD","#FF9800","#01FD55"];
var i = 0;
var approve = false;
var count = 0;
var active = 0;

var x = _("samples");
x.style.marginBottom = "20px";

setInterval(function(){
        if(array_sample.length > 0){
                _("addtogroup").style.color = array_color[active];
                _("addtogroup").children[0].className = "fa fa-upload fa-rotate-90";
        }else{
                _("addtogroup").style.color = null;
                _("addtogroup").children[0].className = "fa fa-upload fa-rotate-180";
        }
},1000);

setInterval(function(){
        if(active !== 0){
            _("capsule-"+active).style.borderColor = array_color[active];
        }else{
          _("capsule-0").style.borderColor = array_color[0];
        }
},1000);

function loadGroupBar(el){
        var toggle = el.getAttribute("toggle");
        switch(toggle){
                case 'open':
                      _("boxBottomControl").style.height = "300px";
                      var x = _("samples");
                      x.style.marginBottom = "300px";
                      el.setAttribute("toggle","close");
                      el.innerHTML = "Close Compare";
                break;
                case 'close':
                      _("boxBottomControl").style.height = "0px";
                      var x = _("samples");
                      x.style.marginBottom = "20px";
                      el.setAttribute("toggle","open");
                      el.innerHTML = "Compare";
                break;
        }
}

function addGroup(){
        approve = false;
        var dataCount = _("addSign");
        var count = dataCount.getAttribute("data-count");
        var length = array_group.length;
        array_group[length] = length;
        var length_new = array_group.length;
        _("addSign").setAttribute("data-count", length_new);
        _("wrapperGroup").setAttribute("data-count",length_new);
                var kidBox = _('wrapperGroup').children;
                for( i = 0 ; i < kidBox.length; i++){
                        kidBox[i].setAttribute("group-no",i);
                }
        layoutAppend(count);
}

function layoutAppend(count){
        var nameCount = parseInt(count) + 1;
        var layout = "<div class='capsule' group-no = '"+ count +"' onClick='activateGroup(this)' id='capsule-"+count+"' style = 'border-color:"+array_color[count]+"'><div class='capsuletoggle'><div class='groupName'><input id='groupNameEditable_"+count+"' class='form-switch groupNameEditable' value='Group "+(nameCount)+"' placeholder='Group Name'/></div></div><div class='dropPort'  id='dropPort_"+count+"' ondrop='drop(event)' ondragover='allowDrop(event)'></div></div>";
        active = count;
        recolor(function(){
          $(".wrapperGroup").append(layout);
        });
}

function activateGroup(el){
        approve = false;
        active = el.getAttribute("group-no");
        recolor();
        setTimeout(function(){
                el.style.borderColor = array_color[active];
        },200);
}

function getindex(sampleId){
        for(var i = 0; i < array_sample.length; i++) {
                for(var j = 0; j < 2; j++){
                        if(array_sample[i][j] === sampleId) {
                            return i;
                        }
                }
        }
}

function checkFromResultingArray(el){
        var sampleId = el.getAttribute("value");
        var sampleName = el.getAttribute("sample-name");
        var label = _("label-"+sampleId);
        if(sampleId !== null && sampleName !== null){
                var proceed = true;
                // Color the label red
                label.style.color = "red";
                // Enabled the checkbox
                el.checked = true;
                for(var i = 0; i < array_sample.length; i++){
                        if(array_sample[i].indexOf(sampleId) >= 0){
                                proceed = false;
                        }
                }
                if(proceed){
                        addArray(sampleName,sampleId);
                }
        }
}

function uncheckFromResultingArray(el){
        var sampleId = el.getAttribute("value");
        var sampleName = el.getAttribute("sample-name");
        var label = _("label-"+sampleId);
        if(sampleId !== null && sampleName !== null){
                // Color the label red
                label.style.color = null;
                // Enabled the checkbox
                el.checked = false;
                deleteArray(sampleName,sampleId);
        }
}

function check(el){
        var sampleId = el.getAttribute("value");
        var sampleName = el.getAttribute("sample-name");
        var label = _("label-"+sampleId);
        if(sampleId !== null && sampleName !== null){
                if(el.checked){
                        label.style.color = "red";
                        addArray(sampleName,sampleId);
                }else{
                        label.style.color = null;
                        deleteArray(sampleName, sampleId);
                }
        }
}

function addArray(sampleName, sampleId){
        var array_length = array_sample.length;
        if(array_length == 0){
                array_sample[0] = new Array (sampleId, sampleName);
        }else{
                array_sample[array_length] = new Array (sampleId, sampleName);
        }
}

function deleteArray(sampleName, sampleId){
        var index = getindex(sampleId);
        if( index != -1){
                var array_length = array_sample.length;
                if(array_length == 0){
                        array_sample.splice(index,1);
                }else{
                        array_sample.splice(index,1);
                }
        }
}

function addfinale(){
        if(active == null){
                alert("Please select a group");
        }else{
                var dropport = _("dropPort_"+active);
                if(array_sample.length != 0){
                        for(var i = 0; i < array_sample.length; i++) {

                                var sampleId = array_sample[i][0];
                                var sampleName = array_sample[i][1];
                                var sampleTableRow = _("sample-row-"+sampleId);
                                sampleTableRow.style.backgroundColor = array_color[active];
                                sampleTableRow.style.opacity = "1";

                                var sampleTableRowCheckBox = _("samplecheckbox-"+sampleId);
                                sampleTableRowCheckBox.disabled = true;

                                var div = document.createElement("div");
                                        div.className = "sampleinnerhold";
                                        div.setAttribute("sampleid", sampleId);
                                        div.setAttribute("samplefield", sampleName);
                                        div.id = "sampleinnerRow"+sampleId;

                                        var deleteDiv = document.createElement("div");
                                                deleteDiv.className = "sampleinnerDelete";
                                                var p = document.createElement("p");
                                                        p.className = "fa fa-times";
                                                deleteDiv.appendChild(p);
                                                deleteDiv.setAttribute("sampleid", sampleId);
                                                deleteDiv.setAttribute("samplefield", sampleName);
                                                deleteDiv.setAttribute("onClick", "deleteSingleRowInGroup(this)");
                                        div.appendChild(deleteDiv);

                                        var nameDiv = document.createElement("div");
                                                nameDiv.className = "sampleinnerName";
                                                var p = document.createElement("p");
                                                        var text = document.createTextNode(sampleName);
                                                        p.appendChild(text);
                                                nameDiv.appendChild(p);
                                        div.appendChild(nameDiv);

                                        dropport.appendChild(div);
                        }
                        array_sample = [];
                }else{
                        alert("Please select a sample");
                }
        }
}

function submitCompareForm(){
  passon(function(){
    if(approve){
        performCompare();
    }
  });
}

function passon(callback){
        var value = "";
        var kid = _('wrapperGroup').children;
        var lengthCount = kid.length;
        if(lengthCount == 1){
                var r = confirm("Would you wish to continue wih just one group ?");
                if(r){
                        var formLength = _('compareAppend').children;
                        if(formLength.length > 0){
                                while(formLength.length){
                                        formLength[0].parentNode.removeChild(formLength[0]);
                                }
                        }
                        var theForm = _('compareAppend');
                        var key = "groupnumber";
                        var value = lengthCount;
                        var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = value;
                        theForm.appendChild(input);

                        var inputGroup = document.createElement('input');
                                inputGroup.type = 'hidden';
                                inputGroup.name = 'groupnames';
                                inputGroup.value = _("groupNameEditable_0").value;
                        theForm.appendChild(inputGroup);

                        var inputGroup = document.createElement('input');
                                inputGroup.type = 'hidden';
                                inputGroup.name = 'groupcolor';
                                inputGroup.value = array_color[0];
                        theForm.appendChild(inputGroup);

                        var subKids = kid[0].children;
                        var kidsDrop = subKids[1].children;

                        if(kidsDrop.length > 0){
                                value = "";
                                for(var j = 0; j < kidsDrop.length ; j++){
                                        var sampleid = kidsDrop[j].getAttribute("sampleid");
                                        if(value == ""){
                                                value = sampleid;
                                        }else{
                                                value += "," + sampleid;
                                        }
                                }
                                var theForm = _('compareAppend');
                                var key = "group-0";
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = value;
                                theForm.appendChild(input);
                                value = "";
                                approve = true;
                        }else{
                                approve = false;
                                value = "";
                                kid[0].style.border = "2px solid red";
                        }
                        recolor();

                        callback(); // Callback function to submit the form

                }else{
                        approve = false;
                }

        }else if(lengthCount > "1"){
                var formLength = _('compareAppend').children;
                if(formLength.length > 0){
                        while(formLength.length){
                                formLength[0].parentNode.removeChild(formLength[0]);
                        }
                }
                var theForm = _('compareAppend');
                var key = "groupnumber";
                var value = lengthCount;
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                theForm.appendChild(input);

                var inputGroup = document.createElement('input');
                        inputGroup.type = 'hidden';
                        inputGroup.name = 'groupnames';
                        var namevalue = "";
                        var names = document.getElementsByClassName("groupNameEditable");
                        for(var i = 0; i< names.length; i++){
                                namevalue += "," + names[i].value;
                        }
                        inputGroup.value = namevalue;
                theForm.appendChild(inputGroup);

                var inputGroup = document.createElement('input');
                        inputGroup.type = 'hidden';
                        inputGroup.name = 'groupcolor';
                        var colorvalue = "";
                        for(var i = 0; i< lengthCount; i++){
                                colorvalue += ","+ array_color[i];
                        }
                        inputGroup.value = colorvalue;
                theForm.appendChild(inputGroup);

                for(var i = 0; i < lengthCount ; i++){
                        var subKids = kid[i].children;
                        var kidsDrop = subKids[1].children;
                        if(kidsDrop.length > 0){
                                value = "";
                                for(var j = 0; j < kidsDrop.length ; j++){
                                        var sampleid = kidsDrop[j].getAttribute("sampleid");
                                        if(value == ""){
                                                value = sampleid;
                                        }else{
                                                value += "," + sampleid;
                                        }
                                }
                                var theForm = _('compareAppend');
                                var key = "group-"+i;
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = key;
                                input.value = value;
                                theForm.appendChild(input);
                                value = "";
                                approve = true;
                        }else{
                                approve = false;
                                value = "";
                                kid[i].style.border = "2px solid red";
                        }
                }
                recolor();

                callback(); // Callback function to submit form

        }else{
                approve = false;
        }
}

// Set All Groups To Default Color

function recolor(callback){
        var kid = _('wrapperGroup').children;
        var lengthCount = kid.length;
        setTimeout(function(){
            for(i = 0; i < lengthCount ; i++){
                kid[i].style.border = "2px solid #CCC";
            }
            if(typeof callback !== 'undefined'){
              callback();
            }
        },100);
}

// Delete Group or Sample function

function deleteSingleRowInGroup(el){
        var sampleid = el.getAttribute("sampleid");

        var sampleTableRowCheckBox = _("samplecheckbox-"+sampleid);
        sampleTableRowCheckBox.disabled = false;
        sampleTableRowCheckBox.checked = false;

        var sampleTableRow = _("sample-row-"+sampleid);
        sampleTableRow.style.backgroundColor = "#FFF";
        sampleTableRow.style.opacity = "1";

        var label = _("label-"+sampleid);
        label.style.color = null;

        var el = _( "sampleinnerRow"+sampleid );
        el.parentNode.removeChild( el );
}

function deleteGroup(){
        var groupCapsule = _("capsule-"+active);
        var r;
        r = confirm("Are you sure you would wish to empty this group ?");
        if(r == true){
                if(active == 0){
                        var sampleCapsule = _("dropPort_"+active).children;
                        var length = sampleCapsule.length;
                        var i = 0;
                        for( var i = 0; i < length; i++){

                            var sampleId = sampleCapsule[i].getAttribute("sampleid");
                            var sampleTableRowCheckBox = _("samplecheckbox-"+sampleId);
                            sampleTableRowCheckBox.disabled = false;
                            sampleTableRowCheckBox.checked = false;
                             _("label-"+sampleId).style.color = null;

                            var sampleTableRow = _("sample-row-"+sampleId);
                            sampleTableRow.style.backgroundColor = "#FFF";
                            sampleTableRow.style.opacity = "1";

                            var label = _("label-"+sampleId);
                            label.style.color = null;

                        }
                        while(sampleCapsule.length){
                            sampleCapsule[0].parentNode.removeChild(sampleCapsule[0]);
                        }
                    }else{

                        var sampleCapsule = _("dropPort_"+active).children;
                        var length = sampleCapsule.length;
                        var i = 0;
                        for( var i = 0; i < length; i++){
                                var sampleId = sampleCapsule[i].getAttribute("sampleid");
                                var sampleTableRowCheckBox = _("samplecheckbox-"+sampleId);
                                console.log("Reaching");
                                sampleTableRowCheckBox.disabled = false;
                                sampleTableRowCheckBox.checked = false;
                                 _("label-"+sampleId).style.color = null;

                                var sampleTableRow = _("sample-row-"+sampleId);
                                sampleTableRow.style.backgroundColor = "#FFF";
                                sampleTableRow.style.opacity = "1";
                        }
                        groupCapsule.parentNode.removeChild(groupCapsule);
                    }
                active = 0;
        }
}

// Submit Form After Callback Final

function performCompare(){
    var form = _("compareForm").submit();
}

// Overlay Single Sample

function showSampleInfo(el){
        var id = el.getAttribute("sampleId");
        $("#SampleDisplayBackDrop").fadeIn(500);
        var xmlhttp;

        if(window.XMLHttpRequest){
                var xmlhttp=new XMLHttpRequest();
        }else{
                var xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }

        xmlhttp.onreadystatechange=function(){
                if(xmlhttp.readyState==4 && xmlhttp.status==200){
                        _("backdropSingleContent").innerHTML = xmlhttp.responseText;
                }
        };

        xmlhttp.open("POST", xhr_root+"index.php/sample/singleView",true);
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        xmlhttp.send("sampleid="+id);
}

function closeSingleDrop(){
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

// Overlay Options

function clusterGroup(){
    $(".backdrop").fadeIn(500);
    var kid = _('wrapperGroup').children;
    for( i = 0; i < kid.length; i++){
            var kidChildren = kid[i].children;
            var childrenSample = kidChildren[1].children;
            var childrenSampleLength = childrenSample.length;
            var color = kid[i].getAttribute("group-no");
            var groupname = _("groupNameEditable_"+color).value;
            // Create DIV ** CONT
            var divCont = document.createElement("div");
            divCont.setAttribute("class","cont");
            divCont.setAttribute("style","border:3px solid "+array_color[color]);
                    //Create Inner DIV ** BANNER
                    var divBanner = document.createElement("div");
                    divBanner.setAttribute("class","bannerCluster");
                            var nodeBanner = document.createTextNode(groupname);
                            divBanner.appendChild(nodeBanner);
                            // Appending DIV to Parent DIV
                            divCont.appendChild(divBanner);
                    //Create Inner DIV ** HOLD
                    var divHold = document.createElement("div");
                            divHold.setAttribute("class","holdCluster");
                                    // repeating Second Button
                                    var divButton = document.createElement("div");
                                            divButton.setAttribute("class","buttonCluster");
                                            var para = document.createElement("p");
                                            if(childrenSampleLength > 1){
                                                    var sampleCount = childrenSampleLength + " Samples";
                                            }else{
                                                    var sampleCount = childrenSampleLength + " Sample";
                                            }
                                            var nodeButton = document.createTextNode(sampleCount);
                                            para.appendChild(nodeButton);
                                            divButton.appendChild(para);
                                    divHold.appendChild(divButton);
                            // Appending DIV to Parent DIV
                    divCont.appendChild(divHold);
                    var divSampleHold = document.createElement("div");
                    divSampleHold.setAttribute("class","sampleHoldCluster");
                    //Repeat Making Sample Point Until all children end
                    for (var j = 0; j < childrenSampleLength; j++){
                            var sampleID = childrenSample[j].getAttribute("sampleid");
                            var sampleField = childrenSample[j].getAttribute("samplefield");
                            var samplePoint = document.createElement("div");
                            samplePoint.setAttribute("class","samplePoint");
                                    var para = document.createElement("p");
                                            var lineOne = "Sample Name : "+ sampleField;
                                    var nodeSample = document.createTextNode(lineOne);
                                            para.appendChild(nodeSample);
                                    samplePoint.appendChild(para);
                            divSampleHold.appendChild(samplePoint);
                    }
                    divCont.appendChild(divSampleHold);
            var element = _('append');
            element.appendChild(divCont);
    }
}

function closeDrop(){
    $("#append").empty();
    $(".backdrop").fadeOut(500);
}


/*
    Script for sliding column panel
*/

function panelToggleLeft(el){
    var panel = _("left-side-panel");
    var mask = _("mask-modal-panel").classList.add("mask-visible");
    panel.style.width = "300px";
    _("staticView").style.paddingLeft = "210px";
}

function panelToggleRight(el){
    var panel = _("right-side-panel");
    var mask = _("mask-modal-panel").classList.add("mask-visible");
    panel.style.width = "300px";
    _("staticView").style.paddingRight = "210px";
}

function removePanel(){
    var panelLeft = _("left-side-panel");
    var mask = _("mask-modal-panel").classList.remove("mask-visible");
    panelLeft.style.width = "0px";
    var panelRight = _("right-side-panel");
    panelRight.style.width = "0px";
    _("staticView").style.paddingRight = "0px";
    _("staticView").style.paddingLeft = "0px";
}
