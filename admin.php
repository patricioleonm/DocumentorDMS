<?php
/**
 * $Id$
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
 *
 */

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

require_once(KT_LIB_DIR . '/plugins/KTAdminNavigation.php');

class AdminSplashDispatcher extends KTAdminDispatcher {
    var $category = '';
    var $sSection = 'administration';
    var $oRegistry = null;

    function AdminSplashDispatcher() {
        $this->aBreadcrumbs = array(
            array('url' => KTUtil::getRequestScriptName($_SERVER), 'name' => _kt('Administration')),
        );
        // are we categorised, or not?
        $this->oRegistry =& KTAdminNavigationRegistry::getSingleton();
        parent::KTAdminDispatcher();
    }

    function do_main() {
        if ($this->category !== '') {
            return $this->do_viewCategory();
        };
        
		$KTConfig =& KTConfig::getSingleton();
        $condensed_admin = $KTConfig->get('condensedAdminUI');

        $aAllItems = $this->getOptions();
        
        $this->oPage->title = _kt('Administration') . ': ';
        $oTemplating =& KTTemplating::getSingleton();

        if ($condensed_admin) {
            $oTemplate = $oTemplating->loadTemplate('kt3/admin_fulllist');
        } else {
            $oTemplate = $oTemplating->loadTemplate('kt3/admin_categories');
        }

        $aTemplateData = array(
              'context' => $this,
              'categories' => $categories,
              'all_items' => $aAllItems,
              'assigned_namespaces' => $assigned_namespaces,
              'baseurl' => $_SERVER['PHP_SELF'],
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_viewCategory() {
        // are we categorised, or not?
        $category = KTUtil::arrayGet($_REQUEST, 'fCategory', $this->category);

        //Removing bad documents/fieldmanagement links from the Document Metadata and Workflow Configuration page.
		if ($category == 'documents') {
	        $oPage =& $GLOBALS['main'];			
			$oPage->requireJSResource('resources/js/kt_hideadminlink.js');
		}
        
        $aCategory = $this->oRegistry->getCategory($category);
		
        $aItems = $this->getOptions($aCategory);

        $this->aBreadcrumbs[] = array('name' => $aCategory['title'], 'url' => KTUtil::ktLink('admin.php',$category));
		
        $this->oPage->title = _kt('Administration') . ': ' . $aCategory['title'];
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/admin_items');
        $aTemplateData = array(
              'context' => $this,
              'category' => $aCategory,
              'items' => ($aItems != null) ? $aItems[0] : null,
              'baseurl' =>  $_SERVER['PHP_SELF'],
        );
        return $oTemplate->render($aTemplateData);
    }

    function getOptions($category = null){
        if($category != null){
            $categories[] = $category;
        }else{
            $categories = $this->oRegistry->getCategories();
        }
        
        $aAllItems = null;

        //added by subadmin plugin
        $sql = "SELECT namespace
        FROM subadmin_helper";

        $result = DBUtil::getResultArray($sql);

        foreach($result as $row){
            $assigned_namespaces[] = $row["namespace"];
        }

        $user_id = $this->oUser->getId();

        foreach ($categories as $aCategory) {
			$aItems = $this->oRegistry->getItemsForCategory($aCategory['name']);
			$category['title'] = $aCategory['title'];
            $category["description"] = $aCategory["description"];
            $category['name'] = $aCategory["name"];
			$items = array();
			foreach($aItems as $item){
                if($user_id == 1 || in_array($item["fullname"], $assigned_namespaces)){
                    $items[] = array("title" => $item["title"], "description" => $item["description"], "fullname" => $item["fullname"]);
                }
            }
            asort($items);
            $category['items'] = $items;
            if(count($category["items"])>0){
                $aAllItems[] = $category;
            }			
        }
        return $aAllItems;
    }
}

$sub_url = KTUtil::arrayGet($_SERVER, 'PATH_INFO');

$sub_url = trim($sub_url);
$sub_url= trim($sub_url, '/');

if (empty($sub_url)) {
    $oDispatcher = new AdminSplashDispatcher();
} else {
    $oRegistry =& KTAdminNavigationRegistry::getSingleton();
    if ($oRegistry->isRegistered($sub_url)) {
       $oDispatcher = $oRegistry->getDispatcher($sub_url);

       $aParts = explode('/',$sub_url);

       $oRegistry =& KTAdminNavigationRegistry::getSingleton();
       $aCategory = $oRegistry->getCategory($aParts[0]);

       $oDispatcher->aBreadcrumbs = array();
       $oDispatcher->aBreadcrumbs[] = array('action' => 'administration', 'name' => _kt('Administration'));
       $oDispatcher->aBreadcrumbs[] = array('name' => $aCategory['title'], 'url' => KTUtil::ktLink('admin.php',$aParts[0]));

    } else {
       // FIXME (minor) redirect to no-suburl?
       $oDispatcher = new AdminSplashDispatcher();
       $oDispatcher->category = $sub_url;
    }
}

// Implement an electronic signature for accessing the admin section, it will appear every 10 minutes
global $main;
global $default;
if($default->enableAdminSignatures && $_SESSION['electronic_signature_time'] < time()){
    $sBaseUrl = KTUtil::kt_url();
    $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    $heading = _kt('You are attempting to access Administration');
    $main->setBodyOnload("javascript: showSignatureForm('{$sUrl}', '{$heading}', 'dms.administration.administration_section_access', 'admin', '{$sBaseUrl}/browse.php', 'close');");
}


$oDispatcher->dispatch(); // we _may_ be redirected at this point (see KTAdminNavigation)

?>
