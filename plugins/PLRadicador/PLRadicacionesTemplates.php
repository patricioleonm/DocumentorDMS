<?php
//Configuración de las radicaciones
include_once(KT_DIR.'/plugins/PLRadicador/PLRadicador-util.php');
class PLRadicacionesTemplatesDispatcher extends KTAdminDispatcher {
    function do_main(){
    	
        $this->aBreadcrumbs[] = array(
            'name' => "Administrar plantillas",
            'url' => $_SERVER["PHP_SELF"],
        );
        $aData = array();
		//si type tiene valor inserta o actualiza
		if($_POST['type'] == 'new'){
			$query = 'INSERT INTO plrradicaciontemplates (radicacion_id, documento_id, descripcion)VALUES(?,?,?)';
			$aParams = array(
				$_POST['rad_id'],
				$_POST['documento'],
				$_POST['descripcion']
			);
			
			$res = DBUtil::runQuery(array($query, $aParams));
			if(PEAR::isError($res)){
				$this->addErrorMessage(sprintf('No se pudo salvar la asociación : %s', $res->getMessage()));
			}else{
				$this->addInfoMessage('La asociación se creo correctamente.');
			}
		}
		
		if($_POST['type'] == 'update'){
			$query = 'UPDATE plrradicaciontemplates 
					SET documento_id = ?,
						descripcion = ?
						WHERE radicacion_id = ?';
			$aParams = array(				
				$_POST['documento'],
				$_POST['descripcion'],
				$_POST['rad_id']
			);
			
			$res = DBUtil::runQuery(array($query, $aParams));
			if(PEAR::isError($res)){
				$this->addErrorMessage(sprintf('No se pudo actualizar la asociación : %s', $res->getMessage()));
			}else{
				$this->addInfoMessage('La asociación se actualizo correctamente.');
			}
		}
		
		//si es edicion obtiene los datos de la asociacion
		if($_GET['action'] == 'edit' && !isset($_POST['type'])){			
			$aData['radicacion'] = PLRadicadorUtil::getRadicationTypeById($_GET['rad_id']);
			
			$query = "SELECT * FROM plrradicaciontemplates WHERE radicacion_id = ?";
			$aParam = array($_GET['rad_id']);
			$res = DBUtil::getOneResult(array($query, $aParam));		
			
			if($res != null){
				$aData['type'] = 'update';
				$aData['descripcion'] = $res['descripcion'];
				$aData['documento_id'] = $res['documento_id'];				
			}else{
				$aData['type'] = 'new';
				$aData['descripcion'] = null;
				$aData['documento_id'] = -1;
			}			
		}
		
		if($_GET['action'] == 'delete'){
			$query = "DELETE FROM plrradicaciontemplates WHERE radicacion_id = ?";
			$aParam = array( $_GET['rad_id'] );
			$res = DBUtil::runQuery(array($query, $aParam));
			if(PEAR::isError($res)){
				$this->addErrorMessage(sprintf('No se pudo eliminar la asociación de plantilla y tipo de radicacion : %s', $res->getMessage()));
			}else{
				$this->addInfoMessage('Asociación de plantilla y tipo de documento eliminada.');
			}
		}
		
		//listado de radicaciones
		$query = "SELECT r.id, r.nombre, t.documento_id, d.full_path, t.descripcion  
				FROM `plrradicaciontemplates`  as t RIGHT JOIN plradicadorradicaciones as r ON r.id = t.radicacion_id
				LEFT JOIN documents as d ON t.documento_id = d.id";
		$res = DBUtil::getResultArray($query);		
		$aData['radicaciones'] = $res;
		
		//listado de documentos del tipo 'Plantilla de radicacion'
		$query = "SELECT cv.document_id, cv.filename, round((cv.size/1024)) as size, cv.storage_path
				FROM document_content_version cv LEFT JOIN document_metadata_version mv ON cv.id = mv.content_version_id
				LEFT JOIN document_types_lookup tl ON mv.document_type_id = tl.id
				WHERE tl.name = 'Plantilla de radicacion'";
		$res = DBUtil::getResultArray($query);
		$aData['templates'] = $res;
		
		//todo al navegador
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('PLRadicacionesTemplates');
        return $oTemplate->render($aData);
    }
}
?>
