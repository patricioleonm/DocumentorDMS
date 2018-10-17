<?php

class TemplateDocumentsMergeAdmin extends KTAdminDispatcher{

	function predispatch() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Templates Merge - Configuration'));
    }


    function do_main(){
		$oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('merge.admin');
        $aTemplateData = array(
              'context' => $this,
        );
        return $oTemplate->render($aTemplateData);
    }
}