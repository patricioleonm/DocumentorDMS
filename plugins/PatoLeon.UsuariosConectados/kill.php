<?php

require_once('../../config/dmsDefaults.php');
//require_once(KT_LIB_DIR . '/dashboard/NotificationRegistry.inc.php');
//require_once(KT_LIB_DIR . '/dashboard/Notification.inc.php');
//require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

/**
 * Dispatcher for action.php/actionname
 *
 * This dispatcher looks up the action from the Action Registry, and
 * then chains onto that action's dispatcher.
 */

 class PatoLeonKillUserDispatcher extends KTStandardDispatcher {    

    /*function check() {
        return true;
    }*/
	
    function do_main() {	
		$id = KTUtil::arrayGet($_REQUEST, 'id');
		if ($id) {
			return $this->kill();
		}
		$this->addInfoMessage(_kt('La sesion no se encontraba activa.'));
		exit(redirect(generateControllerLink('dashboard')));      
    }
    	
    function kill() {
        $sql ="DELETE FROM active_sessions	WHERE id = ".KTUtil::arrayGet($_REQUEST, 'id');;
		$res = DBUtil::runQuery($sql);
		if(PEAR::isError($res)){
			$this->addInfoMessage(_kt('No se pudo cerrar la sesion.').$sql);
		}else{
			$this->addInfoMessage(_kt('Sesion cerrada.'));
		}
        exit(redirect(generateControllerLink('dashboard')));
    }
}

$dispatcher =& new PatoLeonKillUserDispatcher();
$dispatcher->dispatch();