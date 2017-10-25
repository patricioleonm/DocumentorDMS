<?php
/**
 * $Id$
 *
 * Assists in discovering what needs to be done to upgrade one version
 * of KnowledgeTree to another.
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

require_once(KT_LIB_DIR . '/upgrades/UpgradeItems.inc.php');

function setupAdminDatabase() {
    global $default;
    $dsn = array(
        'phptype'  => $default->dbType,
        'username' => $default->dbAdminUser,
        'password' => $default->dbAdminPass,
        'hostspec' => $default->dbHost,
        'database' => $default->dbName,
        'port' => $default->dbPort,
    );

    $options = array(
        'debug'       => 2,
        'portability' => DB_PORTABILITY_ERRORS,
        'seqname_format' => 'zseq_%s',
    );

    $default->_admindb = &DB::connect($dsn, $options);
    if (PEAR::isError($default->_admindb)) {
        die($default->_admindb->toString());
    }
    $default->_admindb->setFetchMode(DB_FETCHMODE_ASSOC);
    return;
}
setupAdminDatabase();

// {{{ Format of the descriptor
/**
 * Format of the descriptor
 *
 * type*version*phase*simple description for uniqueness
 *
 * type is: sql, function, subupgrade, upgrade
 * version is: 1.2.4, 2.0.0rc5
 * phase is: 0, 1, 0pre.  Phase is _only_ evaluated by describeUpgrades.
 * description is: anything, unique in terms of version and type.
 */
// }}}

// {{{ describeUpgrade
/**
 * Describe the upgrade path between two versions of KnowledgeTree.
 *
 * @param string Original version (e.g., "1.2.4")
 * @param string Current version (e.g., "2.0.2")
 *
 * @return array Array of UpgradeItem describing steps to be taken
 */
function &describeUpgrade ($origVersion, $currVersion) {
    // How to figure out what upgrades to do:
    //
    // 1. Get all SQL upgrades >= origVersion and <= currVersion
    // 2. Get all Function upgrades >= origVersion and <= currVersion
    // 3. Categorise each into version they upgrade to
    // 4. Sort each version subgroup into correct order
    // 5. Add "recordSubUpgrade" for each version there.
    // 6. Add back into one big list again
    // 7. Add "recordUpgrade" for whole thing

    // $recordUpgrade =  array('upgrade*' . $currVersion, 'Upgrade to ' .  $currVersion, null);

    $steps = array();
    foreach (array('SQLUpgradeItem', 'FunctionUpgradeItem') as $itemgen) {
        $f = array($itemgen, 'getUpgrades');
        $ssteps =& call_user_func($f, $origVersion, $currVersion);
        $scount = count($ssteps);
        for ($i = 0; $i < $scount; $i++) {
            $steps[] =& $ssteps[$i];
        }
    }
    $upgradestep =& new RecordUpgradeItem($currVersion, $origVersion);
    $steps[] =& $upgradestep;
    $stepcount = count($steps);
    for ($i = 0; $i < $stepcount; $i++) {
        $step =& $steps[$i];
        $step->setParent($upgradestep);
    }
    usort($steps, 'step_sort_func');

    return $steps;
}
// }}}

// {{{ step_sort_func
function step_sort_func ($obj1, $obj2) {
    // Ugly hack to ensure that upgrade table is made first...
    if ($obj1->name === "2.0.6/create_upgrade_table.sql") {
        return -1;
    }
    if ($obj2->name === "2.0.6/create_upgrade_table.sql") {
        return 1;
    }

    // Priority upgrades run first
    if ($obj1->getPriority() < $obj2->getPriority()) {
        return 1;
    }
    if ($obj1->getPriority() > $obj2->getPriority()) {
        return -1;
    }

    // early version run first
    $res = compare_version($obj1->getVersion(), $obj2->getVersion());
    if ($res !== 0) {
        return $res;
    }
    // Order by phase
    if ($obj1->getPhase() > $obj2->getPhase()) {
        return 1;
    }
    if ($obj1->getPhase() < $obj2->getPhase()) {
        return -1;
    }
    // Order by name
    if ($obj1->name < $obj2->name) {
        return -1;
    }
    if ($obj1->name > $obj2->name) {
        return 1;
    }
    return 0;
}
// }}}

// {{{ compare_version
/**
 * Compares two version numbers and returns a value based on this comparison
 *
 * Using standard software version rules, such as 2.0.5rc1 comes before
 * 2.0.5, and 2.0.5rc1 comes after 2.0.5alpha1, compare two version
 * numbers, and determine which is the higher.
 *
 * XXX: Actually, just does $version1 < $version2
 *
 * @param string First version number
 * @param string Second version number
 *
 * @return int -1, 0, 1
 */
function compare_version($version1, $version2) {
    // XXX: Version comparisons should be better.
    if ($version1 < $version2) {
        return -1;
    }
    if ($version1 > $version2) {
        return 1;
    }
    return 0;
}
// }}}

// {{{ lte_version
/**
 * Quick-hand for checking if a version number is lower-than-or-equal-to
 */
function lte_version($version1, $version2) {
    if (in_array(compare_version($version1, $version2), array(-1, 0))) {
            return true;
    }
    return false;
}
// }}

// {{ gte_version
/**
 * Quick-hand for checking if a version number is greater-than-or-equal-to
 */
function gte_version($version1, $version2) {
    if (in_array(compare_version($version1, $version2), array(0, 1))) {
            return true;
    }
    return false;
}
// }}}

?>
