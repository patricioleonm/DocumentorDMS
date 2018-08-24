<?php

include_once(KT_DIR . '/plugins/PLRadicador/lib/tbs/tbs_class.php');
include_once(KT_DIR . '/plugins/PLRadicador/lib/tbs/plugins/tbs_plugin_opentbs.php');
include_once(KT_DIR . '/plugins/PLRadicador/lib/LabelGenerator.php');

class PLRadicadorUtil {
    static function unserializeLabels($label){
        $label["Orientacion"] = $label["Orientacion"];
        $label["Largo"] = $label["Largo"];
        $label["Alto"] = $label["Alto"];
        $label["CB"] = unserialize($label["CB"]);
        $label["QR"] = unserialize($label["QR"]);
        $label["Fecha"] = unserialize($label["Fecha"]);
        $label["Remitente"] = unserialize($label["Remitente"]);
        $label["Destinatario"] = unserialize($label["Destinatario"]);
        $label["Texto1"] = unserialize($label["Texto1"]);
        $label["Texto2"] = unserialize($label["Texto2"]);
        return $label;
    }

    static function getDocumentTypes(){
        $sql = "SELECT document_type_id
                FROM document_type_fieldsets_link
                WHERE fieldset_id IN(
                SELECT DISTINCT(parent_fieldset)
                FROM document_fields
                WHERE name = 'Codigo de radicacion')";
        $res = DBUtil::getResultArray($sql);
        if(PEAR::isError($res)){
            return false;
        }else{
            $docTypes = array();
            foreach($res as $doc){
                $docTypes[] = $doc["document_type_id"];
            }
            return $docTypes;
        }
    }

    static function getDocTipoRadicacion($doc){
        $docTypeId = $doc->getDocumentTypeId();
        $fieldsets = KTMetadataUtil::fieldsetsForDocument($doc);

        $tiposRadicacion = PLRadicadorUtil::getDocumentTypes();

        $aFieldsetIds=array();
        foreach($fieldsets as $fieldset){
            $aFieldsetIds[] = $fieldset->getId();
        }

        $aTiposRadicacion = PLRadicadorUtil::getRadicationTypes();
        foreach($aTiposRadicacion as $tipoRadicacion){
            foreach($aFieldsetIds as $fieldsetId){
//        	var_dump($fieldsetId." ".unserialize($tipoRadicacion["fieldset_asignados"]));
                if(in_array($fieldsetId,$tipoRadicacion["fieldset_asignados"])){
                    return $tipoRadicacion;
                }
            }
        }
    }

    static function getRadicationTypes(){
        $sql = "SELECT id, nombre, descripcion, fieldset_asignados FROM plradicadorradicaciones";

        $res = DBUtil::getResultArray($sql);
        if(PEAR::isError($res)){
            return null;
        }else{
            for($i=0;$i<count($res);$i++){
                $res[$i]["fieldset_asignados"] = unserialize($res[$i]["fieldset_asignados"]);
            }
            return $res;
        }
    }

    static function getRadicationTypeById($radid){
        $sql = "SELECT id, nombre, descripcion, fieldset_asignados FROM plradicadorradicaciones WHERE id = ?";
        $aParam = array($radid);

        $res = DBUtil::getOneResult(array($sql, $aParam));
        if(PEAR::isError($res)){
            return null;
        }else{
            $res["fieldset_asignados"] = unserialize($res["fieldset_asignados"]);
            return $res;
        }
    }

	static function docIsRadicado($docId){
		$sql = "SELECT Count(*) as count FROM `plradicaciondocuments` WHERE document_id = ?";
		$aParams = array ( $docId );		 
		$res = DBUtil::getOneResult(array($sql, $aParams));
		return $res['count'];				
	}

	static function getMetadata($doc){
	    $mdlist = & DocumentFieldLink::getByDocument($doc);
	    $field_values = array();
	    foreach($mdlist as $oFieldLink){
		$field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
	    }
	    return $field_values;
	}

	static function getQRdata($doc){	
	    $data = PLRadicadorUtil::getMetadata($doc);
	    $strData = "";
	    foreach($data as $key => $value){
		$field = DocumentField::get($key);
		$strData .= $field->getName()." : ".$value."%0D";
	    }
	    $sql = "SELECT fecha_radicacion FROM plradicaciondocuments WHERE document_id =".$doc->getId();
	    $res = DBUtil::getOneResult($sql);
	    $strData .= "Fecha radicacion : ".date('d-m-Y', strtotime($res['fecha_radicacion']))."%0D";
	    $strData .= "Hora radicacion : ".date('H:m A', strtotime($res['fecha_radicacion']));
	    return $strData;
	}

	static function UpdateCodigo($context, $doc, $codigo){
		$doctypeid = $doc->getDocumentTypeId();
		
		//obtiene los fieldset asociados		
		$fieldsets = KTMetadataUtil::fieldsetsForDocument($doc, $doctypeid);
		
        //obtengo valores de la metadata actual        
	    $field_values = PLRadicadorUtil::getMetadata($doc);

        $MDPack = array();
        foreach ($fieldsets as $oFieldset) {
            $fields = $oFieldset->getFields();
            foreach ($fields as $oField){
                if($oField->getName() == 'Codigo de radicacion'){
                    $MDPack[] = array (
                    $oField,
                    $codigo
                    );					
                }
                    
                if(isset($field_values[$oField->getId()])){
                    $MDPack[] = array (
                    $oField,
                    $field_values[$oField->getId()]
                    );
                }					
            }
        }
        $context->startTransaction();
        //$context->update();	
        $core_res = KTDocumentUtil::saveMetadata($doc, $MDPack);
	    $doc->update();
	    $context->commitTransaction();
	}

	static function getLabelConfig($radId){
		//obtener datos del label
        $sql = "SELECT * FROM plradicadorlabels WHERE RadicacionID = ".$radId;
        $aLabel = DBUtil::getOneResult($sql);
		if($aLabel == null){
	        $sql = "SELECT * FROM plradicadorlabels WHERE RadicacionID = -1";
    	    $aLabel = DBUtil::getOneResult($sql);
		}
		$aLabel = PLRadicadorUtil::unserializeLabels($aLabel);
		return $aLabel;
	}

    static function GenerateLabel($docid){
        //obtener datos del documento
        $sql = "SELECT * FROM plradicaciondocuments WHERE document_id =".$docid;
        $aRadicacion = DBUtil::getOneResult($sql);        
		
		$aLabel = PLRadicadorUtil::getLabelConfig($aRadicacion['tipo_radicacion']);

        $Label = new LabelGenerator($aLabel['Orientacion'], $aLabel['Largo'], $aLabel['Alto']);
        if($aLabel['CB'][0] == true){ //codigo 39
            $Label->AddCode39($aRadicacion['codigo_radicacion'],$aLabel['CB'][1],$aLabel['CB'][2]);
        }
        if($aLabel['QR'][0] == true){ //codigo qr
    	//obtiene obj document
    	    $doc = Document::get($docid);
            $Label->AddQR(PLRadicadorUtil::getQRdata($doc),$aLabel['QR'][1],$aLabel['QR'][2]); ///preparar metadata para generar QR
        }
        if($aLabel['Fecha'][0] == true){ //fecha
            $Label->AddText($aRadicacion['fecha_radicacion'],$aLabel['Fecha'][3], $aLabel['Fecha'][1], $aLabel['Fecha'][2]);
        }
        if($aLabel['Remitente'][0] == true){ //remitente
            $Label->AddText($aRadicacion['remitente'],$aLabel['Remitente'][3], $aLabel['Remitente'][1], $aLabel['Remitente'][2]);
        }
        if($aLabel['Destinatario'][0] == true){ //destinatario
            $Label->AddText($aRadicacion['destinatario'],$aLabel['Destinatario'][3], $aLabel['Destinatario'][1], $aLabel['Destinatario'][2]);
        }
        if($aLabel['Texto1'][0] == true){ //texto1
            $Label->AddText($aLabel['Texto1'][3],$aLabel['Texto1'][4], $aLabel['Texto1'][1], $aLabel['Texto1'][2]);
        }
        if($aLabel['Texto2'][0] == true){ //texto2
            $Label->AddText($aLabel['Texto2'][3],$aLabel['Texto2'][4], $aLabel['Texto2'][1], $aLabel['Texto2'][2]);
        }
        $Label->Salvar(KT_DIR .'/plugins/PLRadicador/codigos/'.$aRadicacion['document_id'].'.pdf');
    }

	static function getDocumentoPlantillaId($doc){
		$radicacion = PLRadicadorUtil::getDocTipoRadicacion($doc);
		$query = "SELECT documento_id FROM plrradicaciontemplates WHERE radicacion_id = ?";
		$aParam = array( $radicacion['id'] );
		$res = DBUtil::getOneResult(array($query, $aParam));
		return $res['documento_id'];				
	}

	static function CombinaDoc($oDoc, $data){
		$config = KTConfig::getSingleton();
		$doc_path = $config->get('urls/documentRoot');
		
		$tempPath = KT_DIR.'/plugins/PLRadicador/temp/';
		$temp_file = date('mdYHmsA').'.'.KTMime::getFileType($oDoc->getMimeTypeID());
		
		copy($doc_path.'/'.$oDoc->getStoragePath(), $tempPath.$temp_file);
			$doc = new clsTinyButStrong;
			$doc->SetOption('noerr', true);
			$doc->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
			$doc->LoadTemplate($tempPath.$temp_file, OPENTBS_ALREADY_UTF8);
			//combina campos
			foreach($data as $fieldset_name => $values){
                $doc->MergeField($fieldset_name, $values);
                /*
				foreach($values as $name => $value){
					//$data[$fieldset_name] = array($name = $value);
					$doc->MergeField(trim($fieldset_name), array(trim($name) => $value),false);
					if(trim($name) == 'Codigo de radicacion'){
					    $codigo = $value;
					}
                }
                */						
			}
			
			//obtiene datos para insercción en plantilla
			$sql = "SELECT * FROM plradicaciondocuments WHERE document_id = ".$oDoc->getId();
			$info = DBUtil::getOneResult($sql);
			$user = User::get($info['id_usuario_radicador']);

			//combina constantes
			$doc->MergeField('codigo de radicacion', $info['codigo_radicacion'], false);
			$doc->MergeField('fecha radicacion', date('d-m-Y', strtotime($info['fecha_radicacion'])), false);
			$doc->MergeField('hora radicacion', date('H:m A', strtotime($info['fecha_radicacion'])), false);
			$doc->MergeField('radicador', iconv("UTF-8", "ISO-8859-1",$user->getName()), false);
			$doc->MergeField('fecha', date('d-m-Y'));
			$doc->MergeField('hora', date('H:m A'));
			
			$doc->Show(OPENTBS_FILE, $tempPath.'tmp_'.$temp_file);
			
			unlink($tempPath.$temp_file);
			return('tmp_'.$temp_file);
	}
}
?>