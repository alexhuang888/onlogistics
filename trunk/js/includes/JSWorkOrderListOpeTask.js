/**
 * Permet de changer de vue entre les op�rations et les t�ches d'un ordre de travail.
 * @version $Id: JSWorkOrderListOpeTask.js,v 1.2 2008-05-07 16:36:39 ben Exp $
 */
function toggle(value, OtId){
	var choice = document.forms['formSession'].elements['choice']
	return window.location.href = 'WorkOrderOpeTaskList.php?choice=' + 
   		choice.options[choice.selectedIndex].value + '&OtId=' + OtId;
}
