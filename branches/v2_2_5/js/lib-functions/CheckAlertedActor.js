function checkAlertedActor(OperationList,old_actor){
	// Parcours des Op�ration
	for(op=0; op<OperationList.getCount();op++){
		
		// Parcours des t�ches
		for (tsk=0; tsk<OperationList.getItem(op).getCount();tsk++){
			// R�cup�ration des alertedUsers
			AlertedUsersString = OperationList.getItem(op).getItem(tsk).getAlertedUsers();
			AlertedUsersArray = AlertedUsersString.split("|");
			// Parcours du tableau des AlertedUsers
			for(user=0;user<AlertedUsersArray.length;user++){
				//Parcours du UserList pour retrouver l'objet user correspondant
				for (obj_user=0;obj_user<UserList.getCount();obj_user++){
					if (UserList.getItem(obj_user).id == AlertedUsersArray[user]){
						// On a trouv� l'objet User qui correspond
						break;
					}
				}
				
				if (obj_user != UserList.getCount()){
					// On a trouv� un object User qui correspond
					if (UserList.getItem(obj_user).Actor == old_actor){
						// L'acteur est enregistr� pour une alerte
						return false;
					}
				}
								
			} // for user
		} // for tsk 
	}//for op
	return true;
}
