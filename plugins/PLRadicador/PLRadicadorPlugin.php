<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Pato León
 * Date: 14-08-13
 * Time: 12:02 AM
 * To change this template use File | Settings | File Templates.
 */
class PLRadicadorPlugin extends KTPlugin{
    var $sNamespace = "pl.radicador.plugin";
    //var $sPermissionName = 'ktcore.permissions.write'; //definir cual sera el permiso para radicar
    var $iVersion = 0;
    var $autoRegister = false;
    var $createSQL = true;

    function PLRadicadorPlugin($sFilename = null){
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('PL Radicador');
        $this->dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $this->sSQLDir = $this->dir . 'sql' . DIRECTORY_SEPARATOR;
        return $res;
    }

    function setup(){
        $oConfig =& KTConfig::getSingleton();

        //admin category
        $this->registerAdminCategory('radicador', _kt('Radicación'),
            _kt('Configuraciones relativas a la radicación..'));

        //admin menu
        $this->registerAdminPage('radicaciones', 'PLRadicacionesDispatcher', 'radicador',
            _kt('Administrar radicaciones'), _kt('Relaciona los tipos de radicación con los conjuntos de datos (FieldSets) adecuados.'),
            'PLRadicaciones.php', null);

        $this->registerAdminPage('radicacionesTemplates', 'PLRadicacionesTemplatesDispatcher', 'radicador',
            _kt('Administrar plantillas'), _kt('Carga plantillas de documentos(doc, dox, odt) para ser combinadas con metadata.'),
            'PLRadicacionesTemplates.php', null);

        $this->registerAdminPage('radicacionesLabels', 'PLRadicacionesLabelsDispatcher', 'radicador',
            _kt('Configurar etiquetas'), _kt('Configura el tamaño, distribución y campos de la etiqueta.'),
            'PLRadicacionesLabels.php', null);

        //register templates
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('pl.radicador.plugin','/plugins/PLRadicador/templates');

        //trigger
        $this->registerTrigger('delete', 'postValidate', 'PLRadicadorDeleteTrigger','PLRadicador.Trigger.Delete', 'PLRadicadorDeleteTrigger.php');

        //document actions
        $this->registerAction('documentinfo', 'PLRadicadorInfoAction', 'PLRadicador.Actions.Info', 'PLRadicadorActions.php');
        $this->registerAction('documentaction', 'PLRadicadorRadicarAction', 'PLRadicador.Actions.Radicar', 'PLRadicadorActions.php');
        $this->registerAction('documentaction', 'PLRadicadorCombinarAction', 'PLRadicador.Actions.Combinar', 'PLRadicadorActions.php');

    }

}
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('PLRadicadorPlugin', 'pl.radicador.plugin', __FILE__);
?>