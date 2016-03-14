// @author Taha Mazher Topiwala

function SelectionOptions(columns){
	this.columns = columns;
	this.keys = [];
	this.selectionStringColumn = "";
	this.selectionStringEquality = "";
}

SelectionOptions.prototype.StringBuildColumn = function(){

	for (var key in this.columns) {
		if (this.columns.hasOwnProperty(key)) {
			this.keys.push(key);
		}
	}

	for(var i = 0; i < Object.keys(this.columns).length; i++){
		var key = this.keys[i];
		var value = this.columns[key];
		this.selectionStringColumn += "<optgroup label = '"+key+"'>";
		for (var j = 0; j < value.length; j++){
			if(value[j] !== "Unique"){
				this.selectionStringColumn += "<option value = '"+value[j]+"'>"+value[j]+"</option>";
			}
		}
		this.selectionStringColumn += "</optgroup>";
	}
	return this.selectionStringColumn;
}

SelectionOptions.prototype.StringBuildEquality = function () {

	this.selectionStringEquality += "<option value='0'>Equality</option>";
	this.selectionStringEquality += "<option value='='>=</option>";
	this.selectionStringEquality += "<option value='>'>></option>";
	this.selectionStringEquality += "<option value='<'><</option>";
	this.selectionStringEquality += "<option value='>'>>=</option>";
	this.selectionStringEquality += "<option value='<'><=</option>";

	return this.selectionStringEquality;
}

var Selection = new SelectionOptions(Master_All_Columns);
var Column_Selection = Selection.StringBuildColumn(); // Make String of All Columns with its Database value
var Equality_Selection = Selection.StringBuildEquality(); // Make String of All Equalties

function openDetailedSearchBar(el) {
	var toggle = el.getAttribute("toggle-stat");
	var searchBar = _("search-bar-direct");
	var bar = _("detailed-search-bar");
	switch (toggle) {

		case 'close':
			bar.style.height = "275px";
			el.innerHTML = "Close Detailed";
			bar.style.borderColor = "#CCC";
			el.setAttribute("toggle-stat", 'open');
			searchBar.disabled = true;
			clearConditionBar();
		break;

		case 'open':
			bar.style.height = "0px";
			el.innerHTML = "Detailed Search";
			bar.style.borderColor = "transparent";
			el.setAttribute("toggle-stat", 'close');
			searchBar.disabled = false;
			clearConditionBar();
		break;

		default:
			bar.style.height = "0px";
			el.innerHTML = "Detailed Search";
			bar.style.borderColor = "transparent";
			el.setAttribute("toggle-stat", 'close');
			searchBar.disabled = false;
			clearConditionBar();
		break;

	}
}

function Framework (count, masterTarget, bartarget){
	this.count = count;
	this.masterTarget = masterTarget;
	this.conditionalBarTarget = bartarget;
}

Framework.prototype.masterFrame = function () {
	var frame = "<div class = 'conditional_bar' id = 'conditional_bar_"+this.count+"' data-identifier-count = '"+this.count+"'></div>";

	$(this.masterTarget).append(frame);

	this.conditionalBarTarget = _(this.conditionalBarTarget);

	this.columnFrame();
}

Framework.prototype.columnFrame = function () {

	var frame = "<div class = 'selectBox'><div class = 'select-style'><select id = 'column_selection_"+this.count+"'><option value = '0'>Column</option>"+Column_Selection+"</select></div></div>";

	$(this.conditionalBarTarget).append(frame);

	this.equalityFrame();
}

Framework.prototype.equalityFrame = function () {

	var equalityFrame = "<div class = 'selectBox'><div class = 'select-style'><select id = 'equality_selection_"+this.count+"'>"+Equality_Selection+"</select></div></div>";

	$(this.conditionalBarTarget).append(equalityFrame);

	this.conditionFrame();
}

Framework.prototype.conditionFrame = function () {

	var frame = "<input class = 'detail_control_bar_input' placeholder = 'Enter Value' id = 'input_selection_"+this.count+"' />";

	$(this.conditionalBarTarget).append(frame);

	this.deleteConditionFrame();
}

Framework.prototype.deleteConditionFrame = function () {

	var frame  = "<button class = 'detail_control_bar_button_delete' data-conditional-identifier = '"+this.count+"' onclick='deleteConditionalBlock(this)'>x</button>";

	$(this.conditionalBarTarget).append(frame);
}

function addConditionBar(el){

	var content_bar = _("content_bar");
	var count = parseInt(content_bar.getAttribute("data-conditional-count")) + 1;

	var barTarget = "conditional_bar_"+count;

	var frame = new Framework(count, content_bar, barTarget);

	frame.masterFrame();

	content_bar.setAttribute("data-conditional-count",count);

}

function clearConditionBar(el) {

	var content_bar = _("content_bar");

	var contentChildren = content_bar.children;

	while(contentChildren.length){
		contentChildren[0].parentNode.removeChild(contentChildren[0]);
    }

	content_bar.setAttribute("data-conditional-count",0);

	var barTarget = "conditional_bar_0";

	var frame = new Framework(0, content_bar, barTarget);
	frame.masterFrame();

	if(search_box.getAttribute("data-condition") === null){
		resetSearchResultsToDefault();
	}else{
		showStudy(search_box.getAttribute("data-condition"));
	}


}

function deleteConditionalBlock(el) {

	var identifier = el.getAttribute("data-conditional-identifier");
	$("#conditional_bar_"+identifier).remove();
	var content_bar = _("content_bar");

	if(content_bar.children.length < 1){
		content_bar.setAttribute("data-conditional-count",0);

		var barTarget = "conditional_bar_0";

		var frame = new Framework(0, content_bar, barTarget);
		frame.masterFrame();
	}

}

function performDetailedSearch() {
	var columnSelectedValue = "";
	var conditionSelectedValue = "";
	var targetValue = "";
	var resultingArray = [];
	var j = 0;
	var content_bar = _("content_bar");
	var children = content_bar.children;
	for (var i = 0; i < children.length ; i++){
		var child = children[i];
		var data_identifier = child.getAttribute("data-identifier-count");

		columnSelectedValue = _("column_selection_"+data_identifier);
		columnSelectedValue = columnSelectedValue.options[columnSelectedValue.selectedIndex].value;
		columnSelectedValue = "`" + columnSelectedValue.replace(/%/, "%25", 1) + "`";

		if (columnSelectedValue == 0){

			_("column_selection_"+data_identifier).parentNode.style.borderColor = "red";

			setTimeout(function () {
				_("column_selection_"+data_identifier).parentNode.style.borderColor = "black";
			}, 2000);

			break;

		}else{

			resultingArray[j] = columnSelectedValue;
			j++;

			conditionSelectedValue = _("equality_selection_"+data_identifier);
			conditionSelectedValue = conditionSelectedValue.options[conditionSelectedValue.selectedIndex].value;

			if (conditionSelectedValue == 0){

				_("equality_selection_"+data_identifier).parentNode.style.borderColor = "red";

				setTimeout(function () {
					_("equality_selection_"+data_identifier).parentNode.style.borderColor = "black";
				}, 2000);

				break;

			}else{

				resultingArray[j] = conditionSelectedValue;
				j++;

				targetValue = _("input_selection_"+data_identifier).value;

				if (targetValue == ""){

					_("input_selection_"+data_identifier).style.borderColor = "red";

					setTimeout(function () {
						_("input_selection_"+data_identifier).style.borderColor = "#EDEDED";
					}, 2000);

					break;

				}else{

					resultingArray[j] = targetValue;
					j++;

				}
			}
		}
	}

	if ((resultingArray.length % 3 == 0) && resultingArray.length !== 0){
		detailedSearch(resultingArray);
	}
}
