<?php

class TemplateDocumentsZohoAdmin extends KTAdminDispatcher{
    private $config;
    
	function predispatch() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Templates Zoho - Configuration'));
    }

    function do_main(){
        $this->config = $config =& KTConfig::getSingleton();
        $document_types = DocumentType::getList();
        $current_document_type = $this->config->get("KnowledgeTree/zoho_template_doc_type");
        $zoho_api_key = $this->config->get("KnowledgeTree/zoho_api_key");

		$oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('zoho.admin');
        $aTemplateData = array(
              'context' => $this,
              'document_types' => $document_types,
              'zoho_api_key' => $zoho_api_key,
              'current_document_type' => $current_document_type,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_update(){
        $zoho_template_document_type = KTUtil::arrayGet($_POST, 'zoho_template_document_type');
        $zoho_api_key = KTUtil::arrayGet($_POST, 'zoho_api_key');

        if($zoho_api_key == ''){
            mysql_query("UPDATE config_settings SET value = NULL WHERE item = 'zoho_api_key'");
        }else{
            mysql_query(sprintf("UPDATE config_settings SET value = '%s' WHERE item = 'zoho_api_key'", trim($zoho_api_key)));
        }

        if($zoho_template_document_type == '0'){
            mysql_query("UPDATE config_settings SET value = NULL WHERE item = 'zoho_template_doc_type'");
        }else{
            mysql_query(sprintf("UPDATE config_settings SET value = '%s' WHERE item = 'zoho_template_doc_type'", $zoho_template_document_type));
        }

        $this->successRedirectToMain(_kt("Configurations updated"));
        exit(0);
    }
}