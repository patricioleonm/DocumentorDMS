<?php
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
class PatoLeonTemplateDocumentsPlugin extends KTPlugin {
    //var $bAlwaysInclude = TRUE;
    var $sNamespace = 'PatoLeon.TemplateDocumentsPlugin';
    var $showInAdmin = TRUE;
    var $iVersion = 0;
    var $createSQL = TRUE;

    function PatoLeonTemplateDocumentsPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('PatoLeon - Template Documents Plugin');
        $this->dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $this->sSQLDir = $this->dir . 'sql' . DIRECTORY_SEPARATOR;        
        return $res;
    }

    function setup() {
		// Add templates directory to list
		$dir = dirname(__FILE__);
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('PatoLeonTemplateDocumentsPlugin', $dir . DIRECTORY_SEPARATOR . 'templates');

        $this->registerAdminPage('TemplatesZohoAdmin', 'TemplateDocumentsZohoAdmin', 'documents',
        _kt('Document Templates Configurations for Zoho'), _kt('Select a document type to be used as templates with Zoho and Zoho API key'),
        'TemplateDocumentsZohoAdmin.php', null);

        $this->registerAdminPage('TemplatesMergeAdmin', 'TemplateDocumentsMergeAdmin', 'documents',
        _kt('Document Templates Configurations for merge'), _kt('Associate a document type which documents will be used as templates form merge'),
        'TemplateDocumentsMergeAdmin.php', null);


        $this->registerAction('documentaction', 'TemplateDocumentsActionEdit', 'templatedocuments.action.edit', 'TemplateDocumentsActionEdit.php');
        $this->registerAction('documentaction', 'TemplateDocumentsActionGenerate', 'templatedocuments.action.generate', 'TemplateDocumentsActionGenerate.php');

        $this->registerAction('folderaction', 'TemplateDocumentsFolderActionCreate', 'templatedocuments.folderaction.create', 'TemplateDocumentsFolderActionCreate.php');
	}
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('PatoLeonTemplateDocumentsPlugin', 'PatoLeon.TemplateDocumentsPlugin', __FILE__);	
