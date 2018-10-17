<?php
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
class TemplateDocumentsFolderActionCreate extends KTFolderAction{
    var $sName = 'templatedocuments.folderaction.create';
    var $_sShowPermission = "ktcore.permissions.write";
    var $config;

    function getDisplayName() {
        return _kt('Create Document');
    }

    function getButton(){
        $btn = array();
        $btn['display_text'] = _kt('Create Document');
        $btn['arrow_class'] = 'btn btn-warning';
        $btn['icon'] = 'fa fa-file';
        return $btn;
    }

    function do_main(){
        $this->config = $config =& KTConfig::getSingleton();
        $this->oPage->setBreadcrumbDetails(_kt("Create new document"));
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('createdocument');

        $this->oPage->requireJSResource("plugins/PatoLeon.TemplateDocuments/js/templates.js");
        $this->oPage->requireCSSResource("assets/file-icon-vector/file-icon-square-o.min.css");
        $document_types = DocumentType::getList();

        $zoho_document_type = $this->config->get("KnowledgeTree/zoho_template_doc_type");
        $zoho_templates = DBUtil::getResultArray(array("select max(cv.id) as content_id, cv.document_id as id, cv.filename as filename, mt.filetypes as filetype
            from document_content_version cv
            join document_metadata_version dm on  cv.document_id = dm.document_id and dm.document_type_id = ?
            left join mime_types mt on mt.id = cv.mime_id
            group by cv.document_id", array($zoho_document_type)));
            
        $aTemplateData = array(
            'context' => $this,
            'document_types' => $document_types,
            'zoho_templates' => $zoho_templates,
            'merge_templates' => $merge_template,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_create(){
        $type = $_POST["type"];
        $filename = $_POST["filename"];
        $doctype_id = $_POST["doctype_id"];

        $tmp_file = null;
        switch($type){
            case "new":
                $tmp_file = $this->getNewFile($_POST["format"], $filename);
                break;
            case "template":
                $tmp_file = $this->getTemplateFile($_POST["template_id"], $filename);
                break;
            case "merge":
                break;
            default:
                return "error";
        }

        $key = KTUtil::randomString(32);
        $data = array("file" => $tmp_file,
                        "document_name" => $filename,
                        "document_type" => $doctype_id
                    ); 

        $_SESSION['_add_data'] = array($key => $data);

        $url = KTUtil::kt_url().'/action.php?kt_path_info=inet.multiselect.actions.document.addDocument&fFileKey='.$key.'&fFolderId='.$_GET['fFolderId'].'&';

        $fieldsets = $this->getFieldsetsForType($doctype_id);

        if (empty($fieldsets)) {
            $url .= 'action=finalise';
        }else{
            $url .= 'action=metadata';
        }
 //       print_r($data);
  //      exit(0);
        header('Location: '.$url);
    }

    private function getNewFile($format, $filename){
        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");
        $sFilename = tempnam($sBasedir, 'kt_storecontents');
        $oStorage =& KTStorageManagerUtil::getSingleton();
        $oStorage->uploadTmpFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . "docs" . DIRECTORY_SEPARATOR . "blank." . $format, $sFilename);

        $file = array(
            "name" => $filename.".".$format,
            "type" => mime_content_type($sFilename),
            "tmp_name" => $sFilename,
            "error" => 0,
            "size" => filesize($sFilename));
        return $file;
    }

    private function getTemplateFile($template_id, $filename){
        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");
        $sFilename = tempnam($sBasedir, 'kt_storecontents');
        $oStorage =& KTStorageManagerUtil::getSingleton();

        $oDocument = Document::get($template_id);
        $oStorage->uploadTmpFile($oKTConfig->get("urls/documentRoot").DIRECTORY_SEPARATOR.$oDocument->getStoragePath(), $sFilename);

        $fileinfo = pathinfo($oDocument->getFileName());

        return array(
            "name" => $filename . "." . $fileinfo["extension"],
            "type" => mime_content_type($sFilename),
            "tmp_name" => $sFilename,
            "error" => 0,
            "size" => filesize($sFilename)
        );
    }

    private function getMergeTemplate($templateid){

    }

	/**
	 * Get the fieldsets for a particular document type
	 * @return array
	 * @param $iTypeId Object
	 * 
	 * iNET Process
	 */
    function getFieldsetsForType($iTypeId) {
        $typeid = KTUtil::getId($iTypeId);
        $aGenericFieldsetIds = KTFieldset::getGenericFieldsets(array('ids' => false));
        $aSpecificFieldsetIds = KTFieldset::getForDocumentType($typeid, array('ids' => false));

        $fieldsets = kt_array_merge($aGenericFieldsetIds, $aSpecificFieldsetIds);
        return $fieldsets;
    }
}

/* */