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
          alert("Saved");
          $('.saveCompareBackDrop').fadeOut();
        }else{
          callback(false);
          alert("Could not save");
        }
      });
    });
  }
}

function getRandomArbitrary(min, max) {
    return Math.random() * (max - min) + min;
}

function buildDataString(name, callback){
  var data = {};
  data["name"] = name;
  data["group-names"] = MasterGroupNames;
  data["group-id"] = MasterGroupWithID;
  data["group-color"] = MasterColor;
  callback(data);
}

function saveOnDisk(data, callback){
  if (typeof(Storage) !== "undefined") {
    var name = "_#compare-" + getRandomArbitrary(10,1000000);
    localStorage.setItem(name, JSON.stringify(data));
    callback(true);
  } else {
    callback(false);
  }
}
