<?php
include_once("PLRadicador-util.php");
class PLRadicacionesLabelsDispatcher extends KTAdminDispatcher{
    function do_main(){
        //breadcrums
        $this->aBreadcrumbs[] = array(
            'name' => "Configurar etiquetas",
            'url' => $_SERVER["PHP_SELF"],
        );

        $aData = array();
        $aData["context"] = $this;

        //parametros por defecto
        $res = DBUtil::getOneResult("SELECT * FROM plradicadorlabels WHERE RadicacionID = -1");
        $defaultLabel = PLRadicadorUtil::unserializeLabels($res);

        //si es una nueva etiqueta
        if($_POST["action"] == "new"){
            $sql = "INSERT INTO plradicadorlabels(Orientacion,Largo,Alto,CB,QR,Fecha,Texto1,Texto2,RadicacionID)
                    values(?,?,?,?,?,?,?,?,?)";
            $aParams = $this->getPOST();
            $res = DBUtil::runQuery(array($sql,$aParams));
            if(PEAR::isError($res)){
                $this->addErrorMessage(sprintf("No se pudo guardar la configuración de la etiqueta : %s", $res->getMessage()));
            }else{
                $this->addInfoMessage("Configuración de etiqueta guardada.");
            }
        }

        //se es un update de etiqueta
        if($_POST["action"] == "update"){
            $sql = "UPDATE plradicadorlabels
                    SET Orientacion = ?,
                    Largo = ?,
                    Alto = ?,
                    CB = ?,
                    QR = ?,
                    Fecha = ?,
                    Texto1 = ?,
                    Texto2 = ?
                    WHERE RadicacionID = ?";

            $aParams = $this->getPOST();
            $res = DBUtil::runQuery(array($sql,$aParams));
            if(PEAR::isError($res)){
                $this->addErrorMessage(sprintf("No se pudo actualizar la configuración de la etiqueta : %s", $res->getMessage()));
            }else{
                $this->addInfoMessage("Configuración de etiqueta actualizada.");
            }
        }

        //si editar
        if($_GET["action"] == "edit"){
            $aData["rad_id"] = $_GET["id"];
            $res = DBUtil::getOneResult("SELECT R.nombre, R.id,L.Orientacion, L.Largo, L.Alto, L.CB, L.QR, L.Fecha, L.Texto1, L.Texto2
                                        FROM plradicadorlabels L RIGHT JOIN plradicadorradicaciones R ON L.RadicacionID = R.id
                                        WHERE R.id = ".$aData["rad_id"]);
            if(PEAR::isError($res)){
                $this->addErrorMessage("Error al recuperar configuración de etiqueta.");
            }else{
                if($res["Orientacion"] == null){
                    $defaultLabel["nombre"] = $res["nombre"];
                    $aData["action"] = "new";
                }else{
                    $aData["action"] = "update";
                    $defaultLabel = PLRadicadorUtil::unserializeLabels($res);
                }
            }
        }

        //labels configurados
        $res = DBUtil::getResultArray("SELECT R.nombre, R.id,L.Orientacion, L.Largo, L.Alto, L.CB, L.QR, L.Fecha, L.Texto1, L.Texto2
                                        FROM plradicadorlabels L RIGHT JOIN plradicadorradicaciones R ON L.RadicacionID = R.id");
        if(PEAR::isError($res)){
            $this->addErrorMessage("Error al recuperar configuración de etiquetas.");
        }else{
            for($i=0;$i<count($res);$i++){
                $res[$i] = PLRadicadorUtil::unserializeLabels($res[$i]);
            }
            $aData["labels"] = $res;
        }
//var_dump($defaultLabel);
        $aData["defaultLabel"] = $defaultLabel;

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('PLRadicacionesLabels');
        return $oTemplate->render($aData);
    }

    function getPOST(){
        $aParams = array(
            $_POST["orientacion"],
            $_POST["largo"],
            $_POST["alto"],
            serialize(array( //cb
                (isset($_POST["cb"])) ? true : false,
                $_POST["cb_h"],
                $_POST["cb_v"]
            )),
            serialize(array( //qr
                (isset($_POST["qr"])) ? true : false,
                $_POST["qr_h"],
                $_POST["qr_v"]
            )),
            serialize(array( //fecha radicacion
                (isset($_POST["fecha_radicacion"])) ? true : false,
                $_POST["fecha_radicacion_h"],
                $_POST["fecha_radicacion_v"],
                $_POST["fecha_radicacion_color"]
            )),
            serialize(array( //texto1
                (isset($_POST["texto1"])) ? true : false,
                $_POST["texto1_h"],
                $_POST["texto1_v"],
                $_POST["texto1_txt"],
                $_POST["texto1_color"]
            )),
            serialize(array( //texto2
                (isset($_POST["texto2"])) ? true : false,
                $_POST["texto2_h"],
                $_POST["texto2_v"],
                $_POST["texto2_txt"],
                $_POST["texto2_color"]
            )),
            $_POST["rad_id"]
        );
        return $aParams;
    }
}

?>