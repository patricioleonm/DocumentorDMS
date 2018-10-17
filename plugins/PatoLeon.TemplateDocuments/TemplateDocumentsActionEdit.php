<?php


class TemplateDocumentsActionEdit extends KTDocumentAction{
    var $sName = 'templatedocuments.action.edit';
    var $_sShowPermission = "ktcore.permissions.write";
    var $sDisplayName = "Edit Document Online";
    
    function getDisplayName() {
        return $this->sDisplayName;
    }
}