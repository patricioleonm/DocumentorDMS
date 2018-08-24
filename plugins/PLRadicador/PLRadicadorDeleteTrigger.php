<?php

class PLRadicadorDeleteTrigger {

	var $sNamespace = "PLRadicador.Trigger.Delete";
	var $aInfo = null;
	
	function setInfo($aInfo){
		$this->aInfo = $aInfo;
	}

	function postValidate(){		
		$oDoc = $this->aInfo['document'];
		$docId = $oDoc->getId();
		//elimina codigo pdf y png
		$pl_dir = KT_DIR.'/plugins/PLRadicador/codigos/'.$docId;
		if(file_exists($pl_dir.'.pdf')){
		    @unlink($pl_dir.'.pdf');
		}
		if(file_exists($pl_dir.'.png')){
		    @unlink($pl_dir.'.png');
		}
		
		//elimina registro de radicacion de la BD
		$sql = "DELETE FROM plradicaciondocuments WHERE document_id = ". $docId;
		$res = DBUtil::runQuery($sql);
	}
}
?>