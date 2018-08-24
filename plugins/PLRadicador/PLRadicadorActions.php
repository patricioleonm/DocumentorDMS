<?php
//mostrar informacion de radicación
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/database/dbutil.inc');
include_once('PLRadicador-util.php');
class PLRadicadorInfoAction extends KTDocumentAction {
    var $sName = "PLRadicador.Actions.Info";

    function getDisplayName(){
        //obtener datos del documento
        $sql = "SELECT * FROM plradicaciondocuments WHERE document_id =".$this->oDocument->getId();
        $info = DBUtil::getOneResult($sql);
        if($info != null){
            return _kt('Información de radicación');
        }else{
            return false;
        }
    }

    function do_main(){    	
        //obtener datos del documento
        $sql = "SELECT * FROM plradicaciondocuments WHERE document_id =".$this->oDocument->getId();
        $info = DBUtil::getOneResult($sql);
        $aTiporadicacion = PLRadicadorUtil::getRadicationTypeById($info['tipo_radicacion']);
        $aUsuario = User::get($info['id_usuario_radicador']);

        $aData = array();

        $aData['id'] = $this->oDocument->getId();
        $aData['nombre'] = $this->oDocument->getName();
        $aData['codigo'] = $info['codigo_radicacion'];
        $aData['tiporadicacion'] = sprintf('%s (%02s)',$aTiporadicacion['nombre'],$aTiporadicacion['id']);
        $aData['fecha'] = date('d-m-Y H:m A', strtotime($info['fecha_radicacion']));
        $aData['usuario'] = $aUsuario->getName();
        /*$aData['remitente'] = $info['remitente'];
        $aData['destinatario'] = $info['destinatario'];
        $aData['detalle_radicacion'] = $info['detalle_radicacion'];*/
//        var_dump(PLRadicadorUtil::getQRdata($this->oDocument));

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('PLRadicadorInfo');
        return $oTemplate->render($aData);
    }

}

//Generación de la informacion de radicación
class PLRadicadorRadicarAction extends KTDocumentAction {
    var $sName = "PLRadicador.Actions.Radicar";

    function getDisplayName(){
        $oDoc = $this->oDocument;		
        if(in_array($oDoc->getDocumentTypeId(),PLRadicadorUtil::getDocumentTypes()) && PLRadicadorUtil::docIsRadicado($oDoc->getId()) == 0){
            return _kt('Radicar documento');
        }
        return false;
    }

    function do_main(){
	//maximo xorrelativo
        $sql = 'SELECT MAX(correlativo) as correlativo
                FROM `plradicaciondocuments`
                WHERE YEAR(fecha_radicacion) = YEAR(NOW())';
        $correlativo = DBUtil::getOneResult($sql);
        $correlativo['correlativo']++;

        //prepara insert
        $res_codigo_radicacion = PLRadicadorUtil::getDocTipoRadicacion($this->oDocument);
        $id_codigo_radicacion = $res_codigo_radicacion['id'];
        $codigo_radicacion = date('Y').sprintf('%05d', $correlativo['correlativo']).sprintf('%02d', $id_codigo_radicacion);
        $sql = 'INSERT INTO plradicaciondocuments(document_id, id_usuario_radicador, codigo_radicacion, correlativo, tipo_radicacion, fecha_radicacion)values(?,?,?,?,?,now())';
        $aParam = array(
            $this->oDocument->getId(),
            $this->oUser->getId(),
            $codigo_radicacion,
            $correlativo['correlativo'],
            $id_codigo_radicacion
        );

        $res = DBUtil::runQuery(array($sql, $aParam));
        if(PEAR::isError($res)){
            $this->addErrorMessage(sprintf("No se pudo radicar el documento : %s", $res->getMessage()));
	        redirect('view.php?fDocumentId='.$this->oDocument->getId());
        }else{
            //actualiza el codigo en la metadata ????
            PLRadicadorUtil::UpdateCodigo($this, $this->oDocument, $codigo_radicacion);
            //genera label
            PLRadicadorUtil::GenerateLabel($this->oDocument->getid());
            $this->addInfoMessage("Documento radicado correctamente.");
            redirect('action.php?kt_path_info=PLRadicador.Actions.Info&fDocumentId='.$this->oDocument->getId());
        }

/*
        //prepara la plantilla si la radicacion es nueva o edicion
        $aData = array();
        $aData['context'] = $this;
        $aData['action'] = 'new';
        //prepara datos para la radicacion
        $tipoRadicacion = PLRadicadorUtil::getDocTipoRadicacion($this->oDocument);

        $codigoradicacion = date('Y').sprintf('%05d', '11111').sprintf('%02d', $tipoRadicacion['id']);
        $aData['radicacion'] = array(
            'id' => '',
            'docid' => $this->oDocument->getId(),
            'codigo' => $codigoradicacion,
            'nombre' => $tipoRadicacion['nombre'],
            'tiporadicacionid' => $tipoRadicacion['id'],
            'fecha' => date('d-m-Y H:m'),
            'detalle' => '',
            'remitente' => '',
            'destinatario' =>''
        );

        //trata de obtener informacion de radicacion si ya existe
        $sql = "SELECT * FROM `plradicaciondocuments` WHERE  document_id = ?";
        $aParam = array($this->oDocument->getId());

        $res = DBUtil::getOneResult(array($sql, $aParam));
        if(PEAR::isError($res)){
            $this->addErrorMessage('No se pudo obtener información de la radicación.');
        }else{
            if(count($res)>0){
                $aData["radicacion"]['id'] = $res['id'];
                $aData["radicacion"]['docid'] = $res['document_id'];
                $aData["radicacion"]['codigo'] = $res['codigo_radicacion'];
                $aData["radicacion"]['nombre'] = $res['tipo_radicacion'];
                $aData["radicacion"]['tiporadicacionid'] = $tipoRadicacion['id'];
                $aData["radicacion"]['fecha'] = $res['fecha_radicacion'];
                $aData["radicacion"]['detalle'] = $res['detalle_radicacion'];
                $aData["radicacion"]['remitente'] = $res['remitente'];
                $aData["radicacion"]['destinatario'] = $res['destinatario'];
                $aData['action'] = 'update';
            }
        }

        return $oTemplate->render($aData);*/
    }

}

//Combina el documento con la metadata disponible
class PLRadicadorCombinarAction extends KTDocumentAction {
    var $sName = "PLRadicador.Actions.Combinar";


    function getDisplayName(){
        if(PLRadicadorUtil::docIsRadicado($this->oDocument->getId())){
            $mimetypes = array();
            $mimetypes[] = 'docx';
            $mimetypes[] = 'odt';
            $doc_extension = KTMime::getFileType($this->oDocument->getMimeTypeID());
            if(in_array($doc_extension, $mimetypes)){
                return _kt('Combinar documento');
            }else{
                return false;
            }
        }else{
            return null;
        }
    }

    function do_main(){
	    $this->oPage->setBreadcrumbDetails('Descargar plantilla');
    	$oTemplating =& KTTemplating::getSingleton();
    	$oTemplate = $oTemplating->LoadTemplate("PLRadicadorCombinarDownload");
    	//-----recupera metadata actual y la asocia con una array['nombre metadata']['combre campo'] => valor
	    $doctypeid = $this->oDocument->getDocumentTypeId();
	
        //obtiene los fieldset asociados		
        $fieldsets = KTMetadataUtil::fieldsetsForDocument($this->oDocument, $doctypeid);
	
        //obtengo valores de la metadata actual
        $mdlist =& DocumentFieldLink::getByDocument($this->oDocument);
	
        $field_values = array();
        foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
        }

	    $MDPack = array();
        foreach ($fieldsets as $oFieldset) {
            $fields = $oFieldset->getFields();
            foreach ($fields as $oField) {				
                if(isset($field_values[$oField->getId()])){
                    $MDPack[$oFieldset->getName()][$oField->getName()] = $field_values[$oField->getId()];
                }					
	        }
	    }

	//----obtener path absoluto del documento plantilla
//	$id_template_doc = PLRadicadorUtil::getDocumentoPlantillaId($this->oDocument);
//	if($id_template_doc != null){
//	$config = KTConfig::getSingleton();
//        $fsPath = $config->get('urls/documentRoot');
		
//	$template_doc = Document::get($id_template_doc);
//	$template_doc_path = $fsPath."/".$template_doc->getStoragePath().".".$templat_doc->getMimeTypeID();
		
    	// abrir y mezclar documento con la metadata    	
    	$template_combinado = PLRadicadorUtil::CombinaDoc($this->oDocument, $MDPack);
    	$aData['tmp_filename'] = $template_combinado;
    	$aData['filename'] = $this->oDocument->getName();
    	$aData['templateid'] = $id_template_doc;
    	//reemplazar documento actual por documento nuevo, usando checkout->chekin
    	/*
        }else{
    	    $this->addErrorMessage('El tipo de radicacion asociado al documento no posee una plantilla asignada.');
        }*/
        return $oTemplate->render($aData);
    }
    
    function do_download(){
        $tmp_path = KT_DIR."/plugins/PLRadicador/temp/";
        $filename = $tmp_path.$_POST['filename'];
        $size = filesize($filename);
//        $templatedoc = Document::get($_POST['templateid']);
        $mimetype = $this->oDocument->getMimeTypeID();
        $name = $this->oDocument->getName().'.'.KTMime::getFileType($mimetype);
        
        KTUtil::download($filename, $mimetype, $size, $name);
        @unlink($filename);
        exit(0);
    }
}
?>