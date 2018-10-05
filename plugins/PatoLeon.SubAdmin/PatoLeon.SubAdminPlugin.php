<?php
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
class PatoLeonSubAdminPlugin extends KTPlugin {
    var $bAlwaysInclude = TRUE;
    var $sNamespace = 'PatoLeon.SubAdminPlugin';
    var $showInAdmin = TRUE;
    var $iVersion = 0;
    var $createSQL = TRUE;

    function PatoLeonSubAdminPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('PatoLeon - SubAdmin Plugin');
        $this->dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $this->sSQLDir = $this->dir . 'sql' . DIRECTORY_SEPARATOR;        
        return $res;
    }

    function setup() {
		// Add templates directory to list
		$dir = dirname(__FILE__);
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('subadminplugin', $dir . DIRECTORY_SEPARATOR . 'templates');
		$this->setupAdmin();
	}
	
	function setupAdmin() {
		$this->registerAdminPage('SubAdmin', 'PatoLeonSubAdminDispatcher', 'security',
            _kt('Sub Admin'), _kt('Add or remove admin categories and subcategories to subadmin users.'),
			'SubAdminDispatcher.php', null);
	}
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('PatoLeonSubAdminPlugin', 'PatoLeon.SubAdminplugin', __FILE__);	
?>