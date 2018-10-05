<?php

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php'); 
require_once('Muc.class.php'); 

class MucPlugin extends KTPlugin
{
        var $sNamespace = 'Mucplugin.plugin';
        
        function MucPlugin($sFilename = null) 
        {
               $res = parent::KTPlugin($sFilename);
               $this->sFriendlyName = _kt('PatoLeon - Usuario Conectados');
               return $res;
        }

        function setup() 
        {
               $this->registerDashlet('MucDashlet', 'Mucdashlet.dashlet', 'MucDashlet.php');

               require_once(KT_LIB_DIR . "/templating/templating.inc.php");
               $oTemplating =& KTTemplating::getSingleton();
               $oTemplating->addLocation('MucPlugin', '/plugins/PatoLeon.UsuariosConectados/templates');
       }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('MucPlugin', 'Mucplugin.plugin', __FILE__);