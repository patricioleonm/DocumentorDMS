<?php

require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');

class MucDashlet extends KTBaseDashlet 
{
       function MucDashlet ()
       {
               // set the title
               $this->sTitle = _kt('Usuarios Conectados');
      }

	  function is_active($oUser) 
      {
      		  $this->oUser=$oUser;
      			
              // some boolean expression to decide if the dashlet should be displayed to the current user.
              // The root user has an id of 1.
              return ($oUser->getId() == 1) || ($oUser->getUsername() == 'Mucname');        
      }
      
      function render() 
      {
              // we must now render our dashlet.
              $oTemplating =& KTTemplating::getSingleton();
              $oTemplate = $oTemplating->loadTemplate('MucDashlet');
			  

			  //users
			  $data = Muc::getActiveLoginUsers();	
			   
              $aTemplateData = array
              (
                      'is_root' => ($this->oUser->getId() == 1),
					  'userlist' => $data
              );

              return $oTemplate->render($aTemplateData);                
      }
}