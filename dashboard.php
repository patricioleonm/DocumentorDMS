<?php
/**
 * $Id$
 *
 * Main dashboard page -- This page is presented to the user after login.
 * It contains a high level overview of the users subscriptions, checked out
 * document, pending approval routing documents, etc.
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

// main library routines and defaults
require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

require_once(KT_LIB_DIR . '/dashboard/dashletregistry.inc.php');
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

require_once(KT_LIB_DIR . '/dashboard/DashletDisables.inc.php');

$sectionName = 'dashboard';

class DashboardDispatcher extends KTStandardDispatcher {

    var $notifications = array();
    var $sHelpPage = 'ktcore/dashboard.html';

    function DashboardDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'dashboard', 'name' => _kt('Dashboard')),
        );
        return parent::KTStandardDispatcher();
    }
    function do_main() {
        $this->oPage->setShowPortlets(FALSE);
        $this->oPage->hide_section = TRUE;
        $this->oUser->refreshDashboadState();        

        $this->sSection = "dashboard";
        $this->oPage->setBreadcrumbDetails(_kt("Home"));
        $this->oPage->title = _kt("Dashboard");
        $this->oPage->requireCSSResource('assets/css/jquery-ui.min.css');
        $this->oPage->requireJSResource('assets/js/jquery-ui.min.js');
        $this->oPage->requireJSResource('assets/js/dashboard.js');        

        $oDashletRegistry =& KTDashletRegistry::getSingleton();
        $oDashlets = $oDashletRegistry->getDashlets($this->oUser);
        $aDashlets = NULL;        
        $sDashboardState = unserialize($this->oUser->getDashboardState());

        //sort dashlets array based on user preferences
        foreach($oDashlets as $key => $value){
            $dashletNames[] = get_class($value);
        }

        foreach($sDashboardState as $key => $value){
            $index = array_search($key, $dashletNames);
            if(is_numeric($index)){
                $aDashlets[] = $oDashlets[$index];
            }            
        }

        // render
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/dashboard');
        $aTemplateData = array(
              'context' => $this,
              'dashlets' => $aDashlets,
              'dashboard_state' => json_encode($sDashboardState),
        );
        return $oTemplate->render($aTemplateData);
    }

    // return some kind of ID for each dashlet
    // currently uses the class name
    function _getDashletId($oDashlet) {
        return get_class($oDashlet);
    }

    // disable a dashlet.
    // FIXME this very slightly violates the separation of concerns, but its not that flagrant.
    function do_disableDashlet() {
        $sNamespace = KTUtil::arrayGet($_REQUEST, 'fNamespace');
        $iUserId = $this->oUser->getId();

        if (empty($sNamespace)) {
            $this->errorRedirectToMain('No dashlet specified.');
            exit(0);
        }

        // do the "delete"

        $this->startTransaction();
        $aParams = array('sNamespace' => $sNamespace, 'iUserId' => $iUserId);
        $oDD = KTDashletDisable::createFromArray($aParams);
        if (PEAR::isError($oDD)) {
            $this->errorRedirectToMain('Failed to disable the dashlet.');
        }

        $this->commitTransaction();
        $this->successRedirectToMain('Dashlet disabled.');
    }


    function json_saveDashboardState() {
        $sState = KTUtil::arrayGet($_REQUEST, 'state', array('error'=>true));
        $state = json_decode($sState, TRUE);
        $this->oUser->setDashboardState(serialize($state));
        return array('success' => true);
    }
}

$oDispatcher = new DashboardDispatcher();
$oDispatcher->dispatch();

?>

