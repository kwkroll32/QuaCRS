/*
  @author Taha Mazher Topiwala
*/

function closeSaveCompareBackDrop() {
  $('.saveCompareBackDrop').fadeOut();
}

function openSaveCompareBackDrop() {
  $('.saveCompareBackDrop').fadeIn();
}

function performSave(el){
  var input = _("compareName");
  saveCompare(input,function(success){
    if(success){

    }else{
      input.style.borderColor = "red";
      setTimeout(function(){
        input.style.borderColor = null;
      },2000);
    }
  });
}

function saveCompare(el, callback){
  if(el.value === ""){
    callback(false);
  }else{
    buildDataString(el.value, function(data){
      saveOnDisk(data, function(success){
        if(success){
          callback(true);
        }else{
          callback(false);
        }
      });
    });
  }
}

function buildDataString(name, callback){
  var data = {};
  data["name"] = name;
  data["group-names"] = MasterArrayedGroupSampleNames;
  data["group-id"] = MasterGroupWithID;
  data["group-color"] = MasterColor;
  callback(data);
}

function saveOnDisk(data, callback){
  if (typeof(Storage) !== "undefined") {
    if(localStorage.previousCompare){
      localStorage.previousCompare = localStorage.previousCompare + JSON.stringify(data);
    }else{
        localStorage.previousCompare = JSON.stringify(data);
    }
  } else {
    callback(false);
  }
}
