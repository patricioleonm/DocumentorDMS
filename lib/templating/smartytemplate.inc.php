<?php
/**
 * $Id$
 *
 * Smarty Template implementation
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

require_once(KT_LIB_DIR . "/templating/template.inc.php");
require_once(KT_DIR . "/thirdparty/Smarty/Smarty.class.php");
require_once(KT_LIB_DIR . "/i18n/i18nregistry.inc.php");

class KTSmartyTemplate extends KTTemplate {
    function KTSmartyTemplate ($sPath) {
        $this->KTTemplate($sPath);
    }

    function render($aDict = null) {
        $smarty = new Smarty;
        $oConfig =& KTConfig::getSingleton();
        $sVarDirectory = $oConfig->get('urls/varDirectory');
        $smarty->compile_dir = $oConfig->get('urls/tmpDirectory');

//        foreach (array($sVarDirectory . '/tmp', '/tmp') as $sPath) {
//            if (is_writeable($sPath)) {
//                $smarty->compile_dir = $sPath;
//                break;
//            }
//        }
        if (is_array($aDict)) {
            $iLen = count($aDict);
            $aKeys = array_keys($aDict);
            for ($i = 0; $i < $iLen; $i++) {
                $sKey = $aKeys[$i];
                $smarty->assign_by_ref($sKey, $aDict[$sKey]);
            }
        }
        if (is_array($this->aDict)) {
            $iLen = count($this->aDict);
            $aKeys = array_keys($this->aDict);
            for ($i = 0; $i < $iLen; $i++) {
                $sKey = $aKeys[$i];
                $smarty->assign_by_ref($sKey, $this->aDict[$sKey]);
            }
        }
        $KTConfig =& KTConfig::getSingleton();

        // needed for a very, very few places.
        $isSSL = $KTConfig->get("KnowledgeTree/sslEnabled");
        $hostname = $KTConfig->get("KnowledgeTree/serverName");
        $absroot = 'http';
        $absroot .= ($isSSL) ? 's://' : '://';
        $absroot .= $hostname;
        $absroot .= $KTConfig->get("KnowledgeTree/rootUrl");

        if (isset($_SESSION['search2_quick']))
        {
        	$search2_quick = $_SESSION['search2_quick'];
        	$search2_general = $_SESSION['search2_general'];
        	$search2_quickQuery = trim($_SESSION['search2_quickQuery']);
        	if ($search2_quickQuery == '')
        	{
        		$search2_quickQuery = '';
        	}
        }
        else
        {
			$search2_quick=0;
			$search2_general=1;
			$search2_quickQuery = '';
			$_SESSION['search2_quick'] = $search2_quick;
			$_SESSION['search2_general'] = $search2_general;
			$_SESSION['search2_quickQuery'] = '';
        }

        $smarty->assign('search2_anonymous', !array_key_exists('userID', $_SESSION) || ($_SESSION['userID'] == -2));
        $smarty->assign('search2_user', $_SESSION['userId']);
        $smarty->assign('search2_quick', $search2_quick);
        $smarty->assign('search2_general', $search2_general);
        $smarty->assign('search2_quickQuery', $search2_quickQuery);
        $smarty->assign("config", $KTConfig);
        $smarty->assign("appname", $KTConfig->get("ui/appName", "KnowledgeTree"));
        $smarty->assign("rootUrl", $KTConfig->get("KnowledgeTree/rootUrl"));
        $smarty->assign("absoluteRootUrl", $absroot);
        $smarty->caching = false;
        $smarty->register_function('entity_select', array('KTSmartyTemplate', 'entity_select'));
        $smarty->register_function('boolean_checkbox', array('KTSmartyTemplate', 'boolean_checkbox'));
        $smarty->register_function('entity_checkboxes', array('KTSmartyTemplate', 'entity_checkboxes'));
        $smarty->register_function('entity_radios', array('KTSmartyTemplate', 'entity_radios'));
        $smarty->register_block('i18n', array('KTSmartyTemplate', 'i18n_block'), false);
        $smarty->register_modifier('addQueryString', array('KTSmartyTemplate', 'addQueryString'));
        $smarty->register_function('ktLink', array('KTSmartyTemplate', 'ktLink'));
        $smarty->register_modifier('addQS', array('KTSmartyTemplate', 'addQueryString'));
        $smarty->register_modifier('addQueryStringSelf', array('KTSmartyTemplate', 'addQueryStringSelf'));
        $smarty->register_modifier('addQSSelf', array('KTSmartyTemplate', 'addQueryStringSelf'));
        $smarty->register_block('addQS', array('KTSmartyTemplate', 'addQueryStringBlock'), false);
        $smarty->register_function('getUrlForFolder', array('KTSmartyTemplate', 'getUrlForFolder'));
        $smarty->register_function('getCrumbStringForDocument', array('KTSmartyTemplate', 'getCrumbStringForDocument'));
        return $smarty->fetch($this->sPath);
    }

    function _i18n_get_args($arr, $var) {
        if (!is_array($arr)) {
            $arr = array();
        }
        if (substr($var[0], 0, 4) == "arg_") {
            $arr['#' . substr($var[0], 4) . '#'] = $var[1];
        }
        return $arr;
    }

    function i18n_block($params, $content, &$smarty, &$repeat) {
        if (empty($content)) {
            return;
        }
        if (!empty($params)) {
            $flattened = array_map(null, array_keys($params), array_values($params));
            $replacements = array_reduce($flattened, array('KTSmartyTemplate', '_i18n_get_args'), "");
        } else {
            $replacements = array();
        }
        $sDomain = KTUtil::arrayGet($params, 'i18n_domain');
        if (empty($sDomain)) {
            $sDomain = $smarty->get_template_vars('i18n_domain');
        }
        if (empty($sDomain)) {
            $sDomain = 'knowledgeTree';
        }
        $oRegistry =& KTi18nRegistry::getSingleton();
        $oi18n =& $oRegistry->geti18n($sDomain);
        $content = trim($content);
        $replace = array(
            '@ *[\n\r]+@' => ' ',
        );
        $content = preg_replace(array_keys($replace), array_values($replace), $content);

        $content = $oi18n->gettext($content);
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    function addQueryStringBlock($params, $content, &$smarty, &$repeat) {
        $context = KTUtil::arrayGet($params, 'context');
        if (!is_null($context)) {
            $content = $context->meldPersistQuery($content);
        }
        if (empty($content)) {
            return;
        }
        return KTUtil::addQueryStringSelf($content);
    }

    function entity_select ($params, &$smarty) {
        require_once $smarty->_get_plugin_filepath('function', 'html_options');

        $entities = KTUtil::arrayGet($params, 'entities', array());
        /*
        if (is_null($entities)) {
            $smarty->trigger_error("assign: missing 'entities' parameter");
            return;
        }
        */

        $method = KTUtil::arrayGet($params, 'method', 'getName');
        $none = KTUtil::arrayGet($params, 'none');

        $params['values'] = array();
        $params['output'] = array();
        if ($none) {
            $params['values'][] = '';
            $params['output'][] = _kt('None');
        }
        foreach ($entities as $oEntity) {
            $params['values'][] = $oEntity->getId();
            $params['output'][] = call_user_func(array(&$oEntity, $method));
        }
        unset($params['entities']);

        return smarty_function_html_options($params, $smarty);
    }

    function boolean_checkbox ($params, &$smarty) {
        $name = KTUtil::arrayGet($params, 'name');
        if (empty($name)) {
            $smarty->trigger_error("assign: missing 'name' parameter");
            return;
        }
        $bool = KTUtil::arrayGet($params, 'bool');
        if (is_null($bool)) {
            $smarty->trigger_error("assign: missing 'bool' parameter");
            return;
        }
        $value = KTUtil::arrayGet($params, 'value', 1);

        $label = KTUtil::arrayGet($params, 'label');

        if ($bool) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }

        $ret = sprintf('<input type="checkbox" name="%s" value="%s"%s />', $name, $value, $checked);
        if ($label) {
            $ret = sprintf('<label>%s%s</label>', $ret, $label);
        }
        return $ret;
    }

    function entity_checkboxes($params, &$smarty) {
        require_once $smarty->_get_plugin_filepath('function', 'html_checkboxes');

        $entities = KTUtil::arrayGet($params, 'entities');
        if (is_null($entities)) {
            $smarty->trigger_error("assign: missing 'entities' parameter");
            return;
        }

        $idmethod = KTUtil::arrayGet($params, 'idmethod', 'getId');
        $method = KTUtil::arrayGet($params, 'method', 'getName');

        $params['values'] = array();
        $params['output'] = array();
        foreach ($entities as $oEntity) {
            $params['values'][] = call_user_func(array(&$oEntity, $idmethod));
            if ($method != "none") {
                $params['output'][] = ' ' . call_user_func(array(&$oEntity, $method));
            } else {
                $params['output'][] = null;
            }
        }
        unset($params['entities']);
        unset($params['idmethod']);
        unset($params['method']);

        return smarty_function_html_checkboxes($params, $smarty);
    }

    function entity_radios($params, &$smarty) {
        require_once $smarty->_get_plugin_filepath('function', 'html_radios');

        $entities = KTUtil::arrayGet($params, 'entities');
        if (is_null($entities)) {
            $smarty->trigger_error("assign: missing 'entities' parameter");
            return;
        }

        $idmethod = KTUtil::arrayGet($params, 'idmethod', 'getId');
        $method = KTUtil::arrayGet($params, 'method', 'getName');

        $params['values'] = array();
        $params['output'] = array();
        foreach ($entities as $oEntity) {
            $params['values'][] = call_user_func(array(&$oEntity, $idmethod));
            $params['output'][] = call_user_func(array(&$oEntity, $method));
        }
        unset($params['entities']);
        unset($params['idmethod']);
        unset($params['method']);

        return smarty_function_html_radios($params, $smarty);
    }

    // $context is a dispatcher or null
    // if non-null, it will try persist that dispatcher's persist-vars.
    function addQueryString($url, $qs) {
        return KTUtil::addQueryString($url, $qs);
    }


    /*
     * ktLink generates a fully prepared link for KT.
     *
     * It takes into account kt_path_info requirements,
     * rootUrl requirements, etc.
     *
     * To do this effectively, you need three things:
     *
     *   @param base              e.g. /admin.php
     *   @param subpath           e.g. /security/permissions/
     *   @param query             e.g. fFolder=1&fMyVar=2
     *
     */
    function ktLink($params, &$smarty) {
        return KTUtil::ktLink($params['base'], $params['subpath'], $params['query']);
    }


    function addQueryStringSelf($qs) {
        return KTUtil::addQueryStringSelf($qs);
    }

    function getUrlForFolder($params, &$smarty) {
        return KTBrowseUtil::getUrlForFolder($params['folder']);
    }

    function getCrumbStringForDocument($params, &$smarty) {
	$aBreadcrumbs = KTBrowseUtil::breadcrumbsForDocument($params['document'], array('final'=>true));
	if(PEAR::isError($aBreadcrumbs)) {
	    return _kt('No breadcrumbs available');
	}

	$aCrumbs = array();
	foreach($aBreadcrumbs as $aBreadcrumb) {
	    $aCrumbs[] = $aBreadcrumb['name'];
	}

	return implode('/', $aCrumbs);
    }




}

?>
