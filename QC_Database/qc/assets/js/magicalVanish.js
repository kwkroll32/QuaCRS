/*
	@author Taha Mazher Topiwala
*/

var selectorTableTrue = "[data-magicaltable='true']";

var selectorMetricTrue = "[data-magicalmetric='true']";

function vanishTables(el) {
	var toggle = el.getAttribute("data-magictoggle");
	var query = document.querySelectorAll(selectorTableTrue);
	var text = _("magicalVanishTableText");
	switch(toggle){
		case 'vanish':
			el.setAttribute("data-magictoggle", "present");
			el.style.borderColor = "green";
			el.style.color = "red";
			text.innerHTML = "'Null' Tables Hidden";
			for (var i = query.length - 1; i >= 0; i--) {
				fadeOUT(query[i]);
				_("jumpToViewName_"+query[i].id).setAttribute("disabled","true");
			};
		break;
		case 'present':
			el.setAttribute("data-magictoggle", "vanish");
			el.style.borderColor = "#EDEDED";
			el.style.color = "";
			text.innerHTML = "Hide all 'Null' Tables";
			for (var i = query.length - 1; i >= 0; i--) {
				fadeIN(query[i]);
				_("jumpToViewName_"+query[i].id).setAttribute("disabled","false");
			};
		break;
	}
}

function vanishMetrics(el) {
	var toggle = el.getAttribute("data-magictoggle");
	var query = document.querySelectorAll(selectorMetricTrue);
	var text = _("magicalVanishMetricText");
	switch(toggle){
		case 'vanish':
			el.setAttribute("data-magictoggle", "present");
			el.style.borderColor = "green";
			el.style.color = "red";
			text.innerHTML = "'Null' Metrics Hidden";
			for (var i = query.length - 1; i >= 0; i--) {
				fadeOUT(query[i]);
			};
		break;
		case 'present':
			el.setAttribute("data-magictoggle", "vanish");
			el.style.borderColor = "#EDEDED";
			el.style.color = "";
			text.innerHTML = "Hide all 'Null' Metrics";
			for (var i = query.length - 1; i >= 0; i--) {
				fadeIN(query[i]);
			};
		break;
	}
}

function addMagicalTableAttribute(viewname){
	_(viewname).setAttribute("data-magicaltable","true");
}

function fadeOUT(element){
	var i = 1;
    var timer = setInterval(function () {
        if (i <= 0.1){
            clearInterval(timer);
            element.style.display = 'none';
        }
        element.style.opacity = i;
        element.style.filter = 'alpha(opacity=' + i * 100 + ")";
        i -= 0.1;
    }, 50);
}

function fadeIN(element){
	var i = 0.0;
    var timer = setInterval(function () {
        if (i >= 1.0){
            clearInterval(timer);
            element.style.display = 'block';
        }
        element.style.opacity = i;
        element.style.filter = 'alpha(opacity=' + i * 100 + ")";
        i += 0.1;
    }, 50);
}