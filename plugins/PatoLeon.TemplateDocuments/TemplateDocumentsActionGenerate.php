<?php

class TemplateDocumentsActionGenerate extends KTDocumentAction{
    var $sName = 'templatedocuments.action.generate';
    var $_sShowPermission = "ktcore.permissions.write";
    var $sDisplayName = "Generate document";
    
    function getDisplayName() {
        return $this->sDisplayName;
    }
}