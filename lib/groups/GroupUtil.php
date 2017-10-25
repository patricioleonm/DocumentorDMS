<?php
/**
 * $Id$
 *
 * Utility functions regarding groups and membership
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

require_once(KT_LIB_DIR . "/groups/Group.inc");

// {{{ GroupUtil
class GroupUtil {
    // {{{ filterCyclicalGroups
    /**
     * This utility function takes a group whose membership is being
     * considered, and a dictionary with group ids as keys and a list of
     * their member groups as values.
     *
     * $aGroupMembership = array(
     *      1 => array(2, 3, 4),
     *      2 => array(5, 3),
     *      3 => array(5),
     *  }
     *
     * This function returns a list of group ids from the group
     * membership array that may safely to added to the original group.
     */
    // STATIC
    function filterCyclicalGroups ($iTargetGroupID, $aGroupMemberships) {
        $aReturnGroupIDs = array();

        // PHP5: clone/copy
        $aLocalGroupMemberships = $aGroupMemberships;

        // In case we get given ourself, we know we can't add ourselves
        // to each other.
        unset($aLocalGroupMemberships[$iTargetGroupID]);

        // Groups that have no group members can safely be added to the
        // group.  Simplifies debugging of later code.
        foreach ($aLocalGroupMemberships as $k => $v) {
            if (is_null($v) || (!count($v))) {
                unset($aLocalGroupMemberships[$k]);
                $aReturnGroupIDs[] = $k;
            }
        }

        $aBadGroupIDs = GroupUtil::listBadGroups($iTargetGroupID, $aLocalGroupMemberships);

        foreach ($aLocalGroupMemberships as $k => $v) {
            if (!in_array($k, $aBadGroupIDs)) {
                $aReturnGroupIDs[] = $k;
            }
        }

        return $aReturnGroupIDs;
    }
    // }}}

    // {{{
    /**
     * This utility function takes a group whose membership is being
     * considered, and a dictionary with group ids as keys and a list of
     * their member groups as values.
     *
     * $aGroupMembership = array(
     *      1 => array(2, 3, 4),
     *      2 => array(5, 3),
     *      3 => array(5),
     *  }
     *
     * This function returns a list of group ids from the group
     * membership array that can't be safely to added to the original
     * group.
     */
    // STATIC
    function listBadGroups ($iTargetGroupID, $aGroupMemberships) {
        // PHP5: clone/copy
        $aLocalGroupMemberships = $aGroupMemberships;

        // Two ways to do this - either expand the list we're given of
        // immediate children to all children, OR mark group IDs as bad
        // (starting with the group we're planning to add the groups
        // into), and cycle while we're finding new bad groups.
        //
        // Marking bad group IDs seems like the easier-to-understand
        // option.

        $aBadGroupIDs = array($iTargetGroupID);
        $aLastBadGroupCount = 0;

        // While we've discovered new bad groups...
        while (count($aBadGroupIDs) > $aLastBadGroupCount) {
            $aLastBadGroupCount = count($aBadGroupIDs);
            foreach ($aLocalGroupMemberships as $iThisGroupID => $aGroupIDs) {

                // This check isn't strictly necessary, as the groups
                // should be removed from the local list of groups in
                // the later check, but who knows whether one can unset
                // array keys while iterating over the list.

                if (in_array($iThisGroupID, $aBadGroupIDs)) {
                    // print "Not considering $iThisGroupID, it is in bad group list: " . print_r($aBadGroupIDs, true);
                    unset($aLocalGroupMemberships[$iThisGroupID]);
                    continue;
                }

                foreach ($aGroupIDs as $k) {
                    if (in_array($k, $aBadGroupIDs)) {
                        // print "Adding $iThisGroupID to bad list, because it contains $k, which is in bad group list: " .  print_r($aBadGroupIDs, true);
                        unset($aLocalGroupMemberships[$iThisGroupID]);
                        $aBadGroupIDs[] = $iThisGroupID;
                        break;
                    }
                }
            }
        }
        return $aBadGroupIDs;
    }
    // }}}

    // {{{ addGroup
    function addGroup($aGroupDetails) {
        $aDefaultDetails = array(
            "is_unit_admin" => false,
            "is_system_admin" => false,
        );
        $aDetails = kt_array_merge($aDefaultDetails, $aGroupDetails);
        if (is_null(KTUtil::arrayGet($aDetails, "name"))) {
            return PEAR::raiseError("Needed key name is not provided");
        }
        $oGroup = new Group($aDetails["name"],
                $aDetails["is_unit_admin"],
                $aDetails["is_system_admin"]);
        $ret = $oGroup->create();
        if ($ret === false) {
            return PEAR::raiseError(sprintf(_kt("Legacy error creating group, may be: %s"), $_SESSION["errorMessage"]));
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if ($ret !== true) {
            return PEAR::raiseError(_kt("Non-true and non-error return value"));
        }
        return $oGroup;
    }
    // }}}

    // {{{ list
    function listGroups($aGivenOptions = null) {
        if (is_null($aGivenOptions)) {
            $aGivenOptions = array();
        }
        $aDefaultOptions = array(
            //"active" => true,
        );
        $aOptions = kt_array_merge($aDefaultOptions, $aGivenOptions);

        $aWhere = array();
        /* if ($aOptions["active"] === true) {
            $aWhere[] = array("active = ?", true);
        } */

        $sWhere = KTUtil::whereToString($aWhere);

        return Group::getList($sWhere);
    }
    // }}}

    // {{{
    function getNameForID($id) {
        global $default;
        $sName = lookupField($default->groups_table, "name", "id", $id);
        return $sName;
    }
    // }}}

    // {{{ listGroupsForUser
    function listGroupsForUser ($oUser, $aOptions = null) {
        global $default;
        $iUserId = KTUtil::getId($oUser);

        $ids = KTUtil::arrayGet($aOptions, 'ids', false);
	$where = KTUtil::arrayGet($aOptions, 'where', false);

        $sQuery = "SELECT group_id FROM $default->users_groups_table WHERE user_id = ?";
	if($where) {
	    $sQuery .= " AND " . $where;
	}

        $aParams = array($iUserId);
        $aGroupIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), "group_id");
        $aGroups = array();
        foreach ($aGroupIDs as $iGroupID) {
            if ($ids) {
                $aGroups[] = $iGroupID;
                continue;
            }
            $oGroup = Group::get($iGroupID);
            if (PEAR::isError($oGroup)) {
                continue;
            }
            if ($oGroup === false) {
                continue;
            }
            $aGroups[] = $oGroup;
        }
        return $aGroups;
    }
    // }}}

    function _invertGroupArray($aGroupArray) {
        $aRet = array();
        foreach ($aGroupArray as $k => $aArray) {
            foreach ($aArray as $v) {
                $aRet[$v] = KTUtil::arrayGet($aRet, $v, array());
                $aRet[$v][] = $k;
            }
        }
        return $aRet;
    }

    /**
     * Lists all the available sub groups
     */
    function _listSubGroups()
    {
        global $default;
        $sql = 'SELECT parent_group_id, member_group_id FROM '.$default->groups_groups_table;
        $aGroups = DBUtil::getResultArray($sql);

        $aDirectGroups = array();
        if(is_array($aGroups)){
            foreach ($aGroups as $aRow) {
                $aList = KTUtil::arrayGet($aDirectGroups, $aRow['parent_group_id'], array());
                $aList[] = $aRow['member_group_id'];
                $aDirectGroups[$aRow['parent_group_id']] = $aList;
            }
        }

        return GroupUtil::expandGroupArray($aDirectGroups);
    }

    // {{{ _listGroupsIDsForUserExpand
    function _listGroupIDsForUserExpand ($oUser) {
        $iUserId = KTUtil::getId($oUser);
        global $default;
        $oCache = KTCache::getSingleton();
        $group = "groupidsforuser";
        if (PEAR::isError($oUser)) {
            var_dump($oUser);
        }
        list($bCached, $mCached) = $oCache->get($group, $oUser->getId());
        if ($bCached) {
            if (KTLOG_CACHE) $default->log->debug(sprintf("Using group cache for _listGroupIDsForUserExpand %d", $iUserId));
            return $mCached;
        }

        // Get all subgroups
        $aSubGroups = GroupUtil::_listSubGroups();
        $aGroupArray = array();
        if(!empty($aSubGroups)){
            $aGroupArray = GroupUtil::_invertGroupArray($aSubGroups);
        }
        //$aGroupArray = GroupUtil::_invertGroupArray(GroupUtil::buildGroupArray());
        //$aDirectGroups = GroupUtil::listGroupsForUser($oUser);
        $sQuery = "SELECT group_id FROM $default->users_groups_table WHERE user_id = ?";
        $aParams = array($iUserId);
        $aGroupIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), "group_id");
        if(!empty($aGroupArray)){
            foreach ($aGroupIDs as $iGroupID) {
                $aExtraIDs = KTUtil::arrayGet($aGroupArray, $iGroupID);
                if (is_array($aExtraIDs)) {
                    $aGroupIDs = kt_array_merge($aGroupIDs, $aExtraIDs);
                }
            }
        }
        $aGroupIDs = array_unique($aGroupIDs);
        sort($aGroupIDs);
        $oCache->set($group, $oUser->getId(), $aGroupIDs);
        return $aGroupIDs;
    }
    // }}}

    // {{{ listGroupsForUserExpand
    function listGroupsForUserExpand ($oUser, $aOptions = null) {
        $ids = KTUtil::arrayGet($aOptions, 'ids', false);
        $aGroupIDs = GroupUtil::_listGroupIDsForUserExpand($oUser);
        $aGroups = array();
        foreach ($aGroupIDs as $iGroupID) {
            if ($ids) {
                $aGroups[] = $iGroupID;
            }
            $oGroup = Group::get($iGroupID);
            if (PEAR::isError($oGroup)) {
                continue;
            }
            if ($oGroup === false) {
                continue;
            }
            $aGroups[] = $oGroup;
        }
        return $aGroups;
    }
    // }}}

    function checkUserInGroups($iUserId, $aGroupIds) {
        $sGroupIds = implode(', ', $aGroupIds);

        global $default;
        $sTable = $default->users_groups_table;
        $sQuery = "SELECT COUNT(group_id) AS cnt FROM $sTable WHERE user_id = ? AND group_id IN (?)";
        $aParams = array($iUserId, $sGroupIds);

        $res = DBUtil::getOneResult(array($sQuery, $aParams));

        if(PEAR::isError($res) || empty($res)){
            return false;
        }

        if($res['cnt'] > 0){
            return true;
        }
        return false;
    }

    // {{{
    function buildGroupArray() {
        global $default;
        $aDirectGroups = array();
        $aGroupMemberships = DBUtil::getResultArray("SELECT parent_group_id, member_group_id FROM $default->groups_groups_table");
        $aGroups =& Group::getList();
        foreach ($aGroups as $oGroup) {
            $aDirectGroups[$oGroup->getID()] = array();
        }
        foreach ($aGroupMemberships as $aRow) {
            $aList = KTUtil::arrayGet($aDirectGroups, $aRow['parent_group_id'], array());
            $aList[] = $aRow['member_group_id'];
            $aDirectGroups[$aRow['parent_group_id']] = $aList;
        }

        return GroupUtil::expandGroupArray($aDirectGroups);
    }
    // }}}

    // {{{ expandGroupArray
    function expandGroupArray($aDirectGroups) {
        // XXX: PHP5 clone
        if(!is_array($aDirectGroups)){
            return array();
        }

        $aExpandedGroups = $aDirectGroups;
        $iNum = 0;
        foreach ($aExpandedGroups as $k => $v) {
            $iNum += count($v);
        }
        $iLastNum = 0;
        while ($iNum !== $iLastNum) {
            $iLastNum = $iNum;

            foreach ($aExpandedGroups as $k => $v) {
                foreach ($v as $iGroupID) {
                    $aStuff = KTUtil::arrayGet($aExpandedGroups, $iGroupID, null);
                    if (is_null($aStuff)) {
                        continue;
                    }
                    $v = array_unique(kt_array_merge($v, $aStuff));
                    sort($v);
                }
                $aExpandedGroups[$k] = $v;
            }

            $iNum = 0;
            foreach ($aExpandedGroups as $k => $v) {
                $iNum += count($v);
            }
        }
        return $aExpandedGroups;
    }
    // }}}

    // {{{ getMembershipReason
    function getMembershipReason($oUser, $oGroup) {
        $aGroupArray = GroupUtil::buildGroupArray();

        // short circuit

        if ($oGroup->hasMember($oUser)) { return sprintf(_kt('%s is a direct member.'), $oUser->getName()); }


        $aSubgroups = (array) $aGroupArray[$oGroup->getId()];
        if (empty($aSubgroups)) {
            return null; // not a member, no subgroups.
        }

        $sTable = KTUtil::getTableName('users_groups');
        $sQuery = 'SELECT group_id FROM ' . $sTable . ' WHERE user_id = ? AND group_id IN (' . DBUtil::paramArray($aSubgroups) . ')';
        $aParams = array($oUser->getId());
        $aParams = kt_array_merge($aParams, $aSubgroups);

        $res = DBUtil::getOneResult(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        } else if (is_null($res)) {
            return null; // not a member
        } // else {

        $oSubgroup = Group::get($res['group_id']);
        if (PEAR::isError($oSubgroup)) { return $oSubgroup; }

        return sprintf(_kt('%s is a member of %s'), $oUser->getName(), $oSubgroup->getName()); // could be error, but errors are caught.

        // }
    }
    // }}}

    function clearGroupCacheForUser($oUser) {
        $oCache =& KTCache::getSingleton();
        if (PEAR::isError($oUser)) { return $oUser; }
        $group = "groupidsforuser";
        $iUserId = KTUtil::getId($oUser);
        $oCache->remove($group, $iUserId);
    }
}
// }}}

?>
