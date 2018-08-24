<?php
//Configuración de las radicaciones
class PLRadicacionesDispatcher extends KTAdminDispatcher {
    function do_main(){
        $action = "save";
        $rad_id = $_GET["rad_id"];
        $aData = array();

        $this->aBreadcrumbs[] = array(
            'name' => "Administrar radicaciones",
            'url' => $_SERVER["PHP_SELF"],
        );

        if($_POST["action"] == "save"){ //salvar radicacion

            $sql = "INSERT INTO plradicadorradicaciones(nombre,descripcion,fieldset_asignados)VALUES(?,?,?)";
            //sql data
            $aParams = array(
                $_POST["rad_nombre"],
                htmlspecialchars($_POST["rad_descripcion"]),
                (isset($_POST["rad_asig"])) ? serialize($_POST["rad_asig"]) : null,
            );

            $res = DBUtil::runQuery(array($sql, $aParams));
			if (PEAR::isError($res)) {
                $this->addErrorMessage(sprintf(_kt("Tipo de radicación no pudo ser agregada : %s"), $res->getMessage()));
            }else{
                $this->AddInfoMessage(_kt("Tipo de radicación agregada"));
            }
        }

        if($_POST["action"] == "update"){ //actualiza radicacion
            $sql = "UPDATE plradicadorradicaciones SET
              nombre = ?,
              descripcion = ?,
              fieldset_asignados = ?
              WHERE id= ?
            ";

            $aParams = array(
                $_POST["rad_nombre"],
                htmlspecialchars($_POST["rad_descripcion"]),
                (isset($_POST["rad_asig"])) ? serialize($_POST["rad_asig"]) : null,
                $_POST["rad_id"]
            );

            //var_dump($aParams);
            $res = DBUtil::runQuery(array($sql, $aParams));
            if(PEAR::isError($res)){
                $this->addErrorMessage(sprintf("No se pudo actualizar el tipo de radicación : %s", $res->getMessage()));
            }else{
                $this->addInfoMessage("Tipo de radicación actualizada.");
            }
        }

        if($_GET["action"] == "edit"){ //editar radicacion
            $action = "update";
            //obtener fieldsets no asignados
            $sql = "SELECT * FROM plradicadorradicaciones WHERE id=".$_GET["rad_id"];
            $res = DBUtil::getOneResult($sql);
            if (PEAR::isError($res)) {
                return(sprintf(_kt("No se pudo obtener tipo de radicación: %s"), $res->getMessage()));
            }else{
                $aData["rad_nombre"] = $res["nombre"];
                $aData["rad_descripcion"] = $res["descripcion"];
                $aData["rad_asig"] = $this->getFieldsets(unserialize($res["fieldset_asignados"]));
            }
            //var_dump($aData);
        }

        if($_GET["action"] == "delete"){ //eliminar radicacion
            $sql = "DELETE FROM plradicadorradicaciones WHERE id=".$_GET["rad_id"];

            //try to execute sql sentence
            $res = DBUtil::runQuery($sql);
            if (PEAR::isError($res)) {
                $this->addErrorMessage(sprintf(_kt("Tipo de radicación no pudo ser eliminado: %s"), $res->getMessage()));
            }else{
                $this->AddInfoMessage(_kt("Tipo de radicación eliminado"));
            }
        }

      //preparar información para mostrar template-------------------------------

        //obtener fieldsets no asignados
        $aData["fieldsets"] = $this->getFieldsetsNo();

        //obtener listado de radicaciones
        $sql = "SELECT * FROM plradicadorradicaciones";
        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) {
            return(sprintf(_kt("No se pudo obtener listado de radicaciones: %s"), $res->getMessage()));
        }else{
            //convierte lista de fieldsets en forma leible por el usuario
            for($i=0;$i<count($res);$i++){
                $res[$i]["fieldset_asignados"] = join(",", $this->getFieldsets(unserialize($res[$i]["fieldset_asignados"]),false));
            }
            $aData["radicaciones"] = $res;
        }
        //fin obtener radicaciones

        $aData["action"] = $action;
        $aData["rad_id"] = $rad_id;

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('PLRadicaciones');
        return $oTemplate->render($aData);
    }

    function getFieldsetsNo(){
        //obtiene fieldset asignados
        $arrFieldsetsAsignados = array();
        $sql = "SELECT fieldset_asignados FROM plradicadorradicaciones";
        $res = DBUtil::getResultArray($sql);
        foreach($res as $fs){
            if($fs["fieldset_asignados"] != null){
                $arrFieldsetsAsignados = array_merge($arrFieldsetsAsignados,unserialize($fs["fieldset_asignados"]));
            }
        }

        if(count($arrFieldsetsAsignados) == 0){
            $arrFieldsetsAsignados = array(0);
        }
        $sql = "SELECT id, name
            FROM
            fieldsets
            WHERE id IN (
             SELECT DISTINCT(parent_fieldset)
             FROM document_fields
             WHERE name = 'Codigo de radicacion'
             )
             AND id NOT IN (".join(",",$arrFieldsetsAsignados).")";

        $res = DBUtil::getResultArray($sql);
        if (PEAR::isError($res)) {
            return(sprintf(_kt("No se pudo obtener listado de fieldsets: %s"), $res->getMessage()));
        }else{
            return $res;
        }
    }

    function getFieldsets($arrFieldset, $showId = true){
        $arrFieldsets = array();
        foreach($arrFieldset as $fieldset){
            $oFieldset = KTFieldset::get($fieldset);
            if($showId == true){
                $arrFieldsets[] = array(
                    "id" => $fieldset,
                    "name" => $oFieldset->getName()
                );
            }else{
                $arrFieldsets[] = $oFieldset->getName();
            }
        }
        return $arrFieldsets;
    }
}
?>
