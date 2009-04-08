/**
 * @version $Id: ChainActivationTaskDetail.js 9 2008-06-06 09:12:09Z izimobil $
 * @copyright 2005 ATEOR
 **/

 /*
 * Affiche dans le bon <div> le resultat de
 * l'execution renvoyee par le serveur
 **/
function do_getActorSites_cb(htmlContent) {
	var type, content;
	type = htmlContent.substring(0, htmlContent.lastIndexOf('_'));
	content = htmlContent.substring(htmlContent.lastIndexOf('_') + 1);
	$("display" + type + "SiteSelect").innerHTML = content;
}


/*
 * Affiche dans le bon <div> via Sajax un select des Sites de l'Actor selectionne
 * ou tous les Site si pas d'Actor selectionne
 * @param string type : 'Departure' ou 'Arrival'
 **/
function displayActorSites(type) {
	var actorId, fieldName;
	fieldName = "Chain" + type + "Actor";
	actorId = $(fieldName).value;
	x_getActorSites(actorId, type, do_getActorSites_cb);
}

/**
 * callback de updateComponents
 */
function do_updateComponents_cb(htmlContent) {
	var eltname = "ComponentSelect";
	$(eltname).innerHTML = htmlContent;
}

/**
 * Met � jour le widget des Components en fonction
 * du tableau d'ids de nomenclature pass�e en param�tre
 *
 */
function disableEnableDepartureWidgets(type) {
	with (document.forms[0]) {
		DepartureActorType[0].disabled = type;
		DepartureActorType[1].disabled = type;
		DepartureSiteType[0].disabled  = type;
		DepartureSiteType[1].disabled  = type;
		if (type) {
			fw.dom.toggleElement('displayDepartureActorSelect', false);
			fw.dom.toggleElement('displayDepartureSiteSelect', false);
		}
	}
}

function showHideDateLayer() {
	$("displayPlusX_1").style.display  = document.forms[0].elements["WishedDateType"][0].checked?"block":"none";
	$("displayMoinsX_1").style.display = document.forms[0].elements["WishedDateType"][1].checked?"block":"none";
	$("displayPlusX_2").style.display  = document.forms[0].elements["WishedDateType"][2].checked?"block":"none";
	$("displayMoinsX_2").style.display = document.forms[0].elements["WishedDateType"][3].checked?"block":"none";
}

/*
 * Attend la reponse du script cote serveur:
 * Tant que l'element d'id "ChainDepartureSite" n'est pas trouve, on attend
 * Puis selectionne le bon item dans le select
 *
 * Return boolean
 **/
function waitAndDisplayDepartureSite() {
	if (!$("ChainDepartureSite")) {
		setTimeout("waitAndDisplayDepartureSite()", 300);
		return false;
	}
	with(opener.OperationBlockList.getItem(opener._currentOperationIndex).getItem(opener._currentTaskIndex)) {
		SelectItemByValue(document.forms[0].elements["ChainDepartureSite"], getChainDepartureSite());
	}
	return true;
}

/*
 * Attend la reponse du script cote serveur:
 * Tant que l'element d'id "ChainArrivalSite" n'est pas trouve, on attend
 * Puis selectionne le bon item dans le select
 *
 * Return boolean
 **/
function waitAndDisplayArrivalSite() {
	if (!$("ChainArrivalSite")) {
		setTimeout("waitAndDisplayArrivalSite()", 300);
		return false;
	}
	with(opener.OperationBlockList.getItem(opener._currentOperationIndex).getItem(opener._currentTaskIndex)) {
		SelectItemByValue(document.forms[0].elements["ChainArrivalSite"], getChainArrivalSite());
	}
	return true;
}

if(DynAPI.librarypath == ''){
	DynAPI.setLibraryPath('js/dynapi/src/lib/');
	DynAPI.include('dynapi.api.*');
	DynAPI.include('dynapi.ext.inline.js');
}

DynObject.prototype.assign = function(aVar,aContent) {
	this.pattern = "(\\[\\$JS_" + aVar + "\\])";
	this.re = new RegExp(this.pattern,"g");
	this.setHTML(this.getHTML().replace(this.re,aContent));
};

var departureDateWidget, departureHourWidget, departureDayWidget, arrivalDateWidget, arrivalHourWidget, arrivalDayWidget;
var isPivotDate = true;
var ChainTask = opener.OperationBlockList.getItem(opener._currentOperationIndex).getItem(opener._currentTaskIndex)

DynAPI.onLoad = function() {
	var Layer = DynObject.all["TaskDetails"];
	Layer.assign("OpName", getCurrentOperationCaption());
	Layer.assign("TaskName", getCurrentTaskCaption());
	var updateAfter=0;
	with(ChainTask){
		SelectItemByValue(document.forms[0].elements['RessourceGroup'], getRessourceGroup());
		// debut tache d'activation only

		var show = false;
		if(getChainDepartureActor() == 0) {
			document.forms[0].DepartureActorType[0].checked = true;
			show = false;
		}
		else {
			document.forms[0].DepartureActorType[1].checked = true;
			SelectItemByValue(document.forms[0].elements['ChainDepartureActor'], getChainDepartureActor());
			show = true;
		}

		if(getChainDepartureSite() == 0) {
			document.forms[0].DepartureSiteType[0].checked = true;
			show = false;
		}
		else {
			document.forms[0].DepartureSiteType[1].checked = true;
			displayActorSites('Departure');
			waitAndDisplayDepartureSite(ChainTask);
			show = true;
		}

		if(getActivationPerSupplier() == 1) {
			document.forms[0].ActivationPerSupplier.checked = true;
			disableEnableDepartureWidgets(true);
			show = false;
		}
		fw.dom.toggleElement('displayActivationPerSupplier', getProductCommandType()==2)
		fw.dom.toggleElement('displayDepartureSiteSelect', show);

		if(getChainArrivalActor() == 0) {
			document.forms[0].ArrivalActorType[0].checked = true;
			show = true ;
		}
		else {
			document.forms[0].ArrivalActorType[1].checked = true;
			SelectItemByValue(document.forms[0].elements['ChainArrivalActor'], getChainArrivalActor());
			show = true;
		}
		fw.dom.toggleElement('displayArrivalActorSelect', show);

		if(getChainArrivalSite() == 0) {
			document.forms[0].ArrivalSiteType[0].checked = true;
			show = false;
		}
		else {
			document.forms[0].ArrivalSiteType[1].checked = true;
			displayActorSites('Arrival');
			waitAndDisplayArrivalSite(ChainTask);
			show = true;
		}
		fw.dom.toggleElement('displayArrivalSiteSelect', show);

		for(i=0; i<document.forms[0].WishedDateType.length; i++){
			if(document.forms[0].WishedDateType[i].value == getWishedDateType()) {
				document.forms[0].WishedDateType[i].checked = true;
			}
		}
		document.forms[0].Delta.value = getDelta();
		//showHideDateLayer();
		// fin tache d'activation only

		SelectItemByValue(document.forms[0].elements['DurationType'], getDurationType());
		document.forms[0].TaskDurationHour.value  = parseInt(getDuration()/3600);
		document.forms[0].TaskDurationMinute.value  = parseInt((getDuration()-(document.forms[0].TaskDurationHour.value*3600))/60);
		ratioWidget = document.forms[0].elements["ComponentQuantityRatio"];
		for(i=0; i<ratioWidget.length; i++){
			ratioWidget[i].checked = ratioWidget[i].value==getComponentQuantityRatio()?true:false;
		}
	}
}

// Renvoie l'identifiant d'op�ration s�lectionn� correspondant � la PK de la base
function getCurrentSelectedOperationIndex(){
	return opener.OperationBlockList.getItem(opener._currentOperationIndex).getOperationId();
}

function getTaskCaption(taskIndex){
	if(taskIndex != 0){
		return opener.TaskList.getItemById(taskIndex).name;
	}
	return "[Aucune t�che]";
}

// Renvoie l'identifiant de t�che s�lectionn� correspondant � la PK de la base
function getCurrentSelectedTaskIndex(){
	return opener.OperationBlockList.getItem(opener._currentOperationIndex).getItem(opener._currentTaskIndex).id;
}

// Renvoie le libell� de l'op�ration s�lectionn�e
function getCurrentOperationCaption(){
	return opener.OperationList.getItemById(getCurrentSelectedOperationIndex()).name;
}

// Renvoie le libell� de la t�che s�lectionn�e
function getCurrentTaskCaption(){
	return opener.TaskList.getItemById(getCurrentSelectedTaskIndex()).name;
}


function doOk(){
	with(document.forms["TaskDetails"]){
		var curTask = opener.OperationBlockList.getItem(opener._currentOperationIndex).getItem(opener._currentTaskIndex);

		if (elements["TaskDurationHour"].value=="") heure=0;
		else heure = parseInt(elements["TaskDurationHour"].value);
		if (elements["TaskDurationMinute"].value=="") minute=0;
		else minute = parseInt(elements["TaskDurationMinute"].value);
		duree = heure * 3600 + minute *60;
		curTask.setDuration(duree);
		curTask.setRessourceGroup(parseInt(elements["RessourceGroup"].value));

			
		// type de commande
		var hasProductCommandType = true;
		curTask.setHasProductCommandType(hasProductCommandType);
		var aProductCommandType = 2;
		curTask.setProductCommandType(aProductCommandType);

		// DepartureActor
		for(i=0; i<elements["DepartureActorType"].length; i++){
			if(elements["DepartureActorType"][i].checked == true) {
				var aDepartureActorType = parseInt(elements["DepartureActorType"][i].value);
			    if(!elements["DepartureSiteType"][i].checked) {
                      alert('Vous devez choisir un site pour un acteur fix�');
                      return false;
                }
			}
		}
		if(aDepartureActorType == 1){
			curTask.setChainDepartureActor(elements["ChainDepartureActor"].options[elements["ChainDepartureActor"].selectedIndex].value);
		}
		else {
			curTask.setChainDepartureActor(0);
		}
		// DepartureSite
		for(i=0; i<elements["DepartureSiteType"].length; i++){
			if(elements["DepartureSiteType"][i].checked == true) {
				var aDepartureSiteType = parseInt(elements["DepartureSiteType"][i].value);
			}
		}
		if(aDepartureSiteType == 1){
			curTask.setChainDepartureSite(elements["ChainDepartureSite"].options[elements["ChainDepartureSite"].selectedIndex].value);
		}
		else {
			curTask.setChainDepartureSite(0);
		}
		// ArrivalActor
		for(i=0; i<elements["ArrivalActorType"].length; i++){
			if(elements["ArrivalActorType"][i].checked == true) {
				var aArrivalActorType = parseInt(elements["ArrivalActorType"][i].value);
			    if(!elements["ArrivalSiteType"][i].checked) {
                       alert('Vous devez choisir un site pour un acteur fix�');
                       return false;
                }
			}
		}
		if(aArrivalActorType == 1){
			curTask.setChainArrivalActor(elements["ChainArrivalActor"].options[elements["ChainArrivalActor"].selectedIndex].value);
		}
		else {
			curTask.setChainArrivalActor(0);
		}
		// ArrivalSite
		for(i=0; i<elements["ArrivalSiteType"].length; i++){
			if(elements["ArrivalSiteType"][i].checked == true) {
				var aArrivalSiteType = parseInt(elements["ArrivalSiteType"][i].value);
			}
		}
		if(aArrivalSiteType == 1){
			curTask.setChainArrivalSite(elements["ChainArrivalSite"].options[elements["ChainArrivalSite"].selectedIndex].value);
		}
		else {
			curTask.setChainArrivalSite(0);
		}
		// WishedDateType
		for(i=0; i<elements["WishedDateType"].length; i++){
			if(elements["WishedDateType"][i].checked == true) {
				var aWishedDateType = parseInt(elements["WishedDateType"][i].value);
			}
		}
		curTask.setWishedDateType(aWishedDateType);
		curTask.setDelta(parseInt(elements["Delta"].value));
		curTask.setActivationPerSupplier(elements["ActivationPerSupplier"].checked?1:0);
        //
		// nomenclature components and component ratio
		for(i=0; i<elements["ComponentQuantityRatio"].length; i++){
			if(elements["ComponentQuantityRatio"][i].checked == true) {
				var ratio = parseInt(elements["ComponentQuantityRatio"][i].value);
				curTask.setComponentQuantityRatio(ratio);
			}
		}
		// durationtype
		curTask.setDurationTypeLabel(elements["DurationType"].options[elements["DurationType"].selectedIndex].text);
		curTask.setDurationType(parseInt(elements["DurationType"].value));
	}
    document.forms['TaskDetails'].submit();
	opener.renderAll();
	self.close();
}


function FindSelectedValues(widget){
	var return_values = new Array();
	var cpt = 0;
	for (var i=0;i<widget.length;i++){
		if(widget.options[i].selected == true) return_values[cpt++] = widget.options[i].value;
	}
	return return_values;
}

function SelectItemByValue(widget, value){
	for(var i = 0; i < widget.options.length; i++){
		if(widget.options[i].value == value) {
			widget.selectedIndex = i;
		}
	}
}



function issetPivotDate() {
	var curOp = opener.OperationBlockList;
	if(curOp.getPivotOp() != -1 && curOp.getPivotTask() != -1 && curOp.getPivotDate() != -1) {
		return true;
	} else {
		return false;
	}
}

function modifyPivotDate(value) {
	with(document.forms["TaskDetails"]){
	    if(issetPivotDate() && elements["PivotDate"].selectedIndex != 0) {
		    if(!confirm(TaskDetails_4)) {
			    elements["PivotDate"].selectedIndex = 0;
				isPivotDate = false;
		    }
        }
	}
}


