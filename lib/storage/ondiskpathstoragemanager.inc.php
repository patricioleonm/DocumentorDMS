<?php
/**
 * $Id$
 *
 * Provides storage for contents of documents on disk, using the same
 * path on-disk as in the repository.
 *
 * WARNING:
 *
 * This storage manager is _not_ transaction-safe, as on-disk paths need
 * to update when the repository position changes, and this operation
 * and the repository change in combination can't be atomic, even if
 * they individually are.
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

require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
require_once(KT_LIB_DIR . '/mime.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/PhysicalDocumentManager.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');

// used for well-known MIME deterministic techniques
if (!extension_loaded('fileinfo')) {
    @dl('fileinfo.' . PHP_SHLIB_SUFFIX);
}

class KTOnDiskPathStorageManager extends KTStorageManager {
    function upload(&$oDocument, $sTmpFilePath) {
        $oConfig =& KTConfig::getSingleton();
        $sStoragePath = $this->generateStoragePath($oDocument);
        $this->setPath($oDocument, $sStoragePath);
        $oDocument->setFileSize(filesize($sTmpFilePath));
        $sDocumentFileSystemPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
        //copy the file accross
        $start_time = KTUtil::getBenchmarkTime();
        $file_size = $oDocument->getFileSize();
        if (copy($sTmpFilePath, $sDocumentFileSystemPath)) {
            $end_time = KTUtil::getBenchmarkTime();
            global $default;
            $default->log->info(sprintf("Uploaded %d byte file in %.3f seconds", $file_size, $end_time - $start_time));

            //remove the temporary file
            unlink($sTmpFilePath);
            if (file_exists($sDocumentFileSystemPath)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Upload a temporary file
     *
     * @param unknown_type $sUploadedFile
     * @param unknown_type $sTmpFilePath
     * @return unknown
     */
    function uploadTmpFile($sUploadedFile, $sTmpFilePath, $aOptions = null) {

        //copy the file accross
        if (OS_WINDOWS) {
            $sTmpFilePath = str_replace('\\','/',$sTmpFilePath);
        }
        if ($this->writeToFile($sUploadedFile, $sTmpFilePath)) {
            if (file_exists($sTmpFilePath)) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    function getPath(&$oDocument) {
        return $oDocument->getStoragePath();
    }

    function setPath(&$oDocument, $sNewPath) {
        $oDocument->setStoragePath($sNewPath);
    }

    function generateStoragePath(&$oDocument) {
        $sStoragePath = sprintf("%s/%s-%s", Folder::generateFolderPath($oDocument->getFolderID()), $oDocument->getContentVersionId(), $oDocument->getFileName());
        return $sStoragePath;
    }

    function temporaryFile(&$oDocument) {
        $oConfig =& KTConfig::getSingleton();
        return sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));
    }

    function freeTemporaryFile($sPath) {
        return;
    }

    function download($oDocument) {
        //get the path to the document on the server
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oDocument));

        $mimeType = KTMime::getMimeTypeName($oDocument->getMimeTypeID());
        $fileSize = $oDocument->getFileSize();
        $fileName = $oDocument->getFileName();

        return KTUtil::download($sPath, $mimeType, $fileSize, $fileName);
    }

    function createFolder($oFolder) {
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $oFolder->generateFolderPath($oFolder->getID()));
        if (file_exists($sPath)) {
            // It already exists - let's just use it.
            return;
        }
        $res = @mkdir($sPath, 0755);
        if ($res === false) {
            return PEAR::raiseError(_kt("Couldn't create folder"));
        }
        return true;
    }

    function removeFolder($oFolder) {
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $oFolder->generateFolderPath($oFolder->getID()));
        if (!file_exists($sPath)) {
            return true;
        }
        @rmdir($sPath);
        // No point erroring out if the rmdir fails.
        return true;
    }

    function removeFolderTree($oFolder) {
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $oFolder->generateFolderPath($oFolder->getID()));
        KTUtil::deleteDirectory($sPath);
    }

    function downloadVersion($oDocument, $iVersionId) {
        //get the document
        $oContentVersion = KTDocumentContentVersion::get($iVersionId);
        $oConfig =& KTConfig::getSingleton();
        $sPath = sprintf("%s/%s", $oConfig->get('urls/documentRoot'), $this->getPath($oContentVersion));
        $sVersion = sprintf("%d.%d", $oContentVersion->getMajorVersionNumber(), $oContentVersion->getMinorVersionNumber());

        $mimeType = KTMime::getMimeTypeName($oContentVersion->getMimeTypeID());
        $fileSize = $oContentVersion->getFileSize();
        $fileName = $sVersion.'-'.$oContentVersion->getFileName();

        return KTUtil::download($sPath, $mimeType, $fileSize, $fileName);
    }

	/**
 	 * Move a document to a new folder
     *
     * By the time we are called, the document believes it is in the new
     * location in terms of its folder_id and paths.  Just in case, we
     * avoid using generateStoragePath and rely on the folder objects
     * for our paths.
     *
     * We have to use the folders for our source and destination paths,
     * and then set storage_path.
	 *
	 * return boolean true on successful move, false otherwhise
	 */
	function moveDocument(&$oDocument, $oSourceFolder, $oDestinationFolder) {
        $oConfig =& KTConfig::getSingleton();
        $aContentVersions = KTDocumentContentVersion::getByDocument($oDocument);
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        foreach ($aContentVersions as $oVersion) {
            $sOldPath = sprintf("%s/%s-%s", Folder::generateFolderPath($oSourceFolder->getID()), $oVersion->getId(), $oVersion->getFileName());
            $sNewPath = sprintf("%s/%s-%s", Folder::generateFolderPath($oDestinationFolder->getID()), $oVersion->getId(), $oVersion->getFileName());
            $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $sOldPath);
            $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);
            $res = KTUtil::moveFile($sFullOldPath, $sFullNewPath);
            $oVersion->setStoragePath($sNewPath);
            $oVersion->update();
        }
        return true;
	}

	/**
	 * Move a file
	 *
	 * @param string source path
	 * @param string destination path
	 */
	function move($sOldDocumentPath, $sNewDocumentPath) {
		global $default;
		if (file_exists($sOldDocumentPath)) {
			//copy the file	to the new destination
			if (copy($sOldDocumentPath, $sNewDocumentPath)) {
				//delete the old one
				unlink($sOldDocumentPath);
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

    function moveFolder($oFolder, $oDestFolder) {
        $table = "document_content_version";
        $sQuery = "UPDATE $table SET storage_path = CONCAT(?, SUBSTRING(storage_path FROM ?)) WHERE storage_path LIKE ?";

        if ($oDestFolder->getId() == 1) {
            $sDestFolderPath = $oDestFolder->getName();
        } else {
            $sDestFolderPath = sprintf("%s/%s", $oDestFolder->getFullPath(), $oDestFolder->getName());
        }

        if ($oFolder->getId() == 1) {
            $sSrcFolderPath = $oFolder->getName();
        } else {
            $sSrcFolderPath = sprintf("%s/%s", $oFolder->getFullPath(), $oFolder->getName());
        }

        $aParams = array(
            $sDestFolderPath,
            strlen($oFolder->getFullPath()) + 1,
            sprintf("%s%%", $sSrcFolderPath),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $oConfig =& KTConfig::getSingleton();
        $sSrc = sprintf("%s/%s",
            $oConfig->get('urls/documentRoot'),
            $sSrcFolderPath
        );
        $sDst = sprintf("%s/%s/%s",
            $oConfig->get('urls/documentRoot'),
            $sDestFolderPath,
            $oFolder->getName()
        );
        return KTUtil::moveDirectory($sSrc, $sDst);
    }

    function renameFolder($oFolder, $sNewName) {
        $table = "document_content_version";
        $sQuery = "UPDATE $table SET storage_path = CONCAT(?, SUBSTRING(storage_path FROM ?)) WHERE storage_path LIKE ?";

        if ($oFolder->getId() == 1) {
            $sSrcFolderPath = $oFolder->getName();
            $sDestFolderPath = $sNewName;
        } else {
            $sSrcFolderPath = sprintf("%s/%s", $oFolder->getFullPath(), $oFolder->getName());
            $sDestFolderPath = sprintf("%s/%s", $oFolder->getFullPath(), $sNewName);
        }

        $aParams = array(
            $sDestFolderPath,
            strlen($sSrcFolderPath) + 1,
            sprintf("%s%%", $sSrcFolderPath),
        );
        $res = DBUtil::runQuery(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        }

        $oConfig =& KTConfig::getSingleton();
        $sSrc = sprintf("%s/%s",
            $oConfig->get('urls/documentRoot'),
            $sSrcFolderPath
        );
        $sDst = sprintf("%s/%s",
            $oConfig->get('urls/documentRoot'),
            $sDestFolderPath
        );
        $res = @rename($sSrc, $sDst);
		if (PEAR::isError($res) || ($res == false)) {
		    print '<br /> -- unable to move ' . $sSrc . ' to ' . $sDst . '    ';
		    return false;
		    // return PEAR::raiseError('unable to move directory to ' . $sDst);
		}

		return true;
    }

	/**
     * Perform any storage changes necessary to account for a copied
     * document object.
     */
     function copy($oSrcDocument, &$oNewDocument) {
        // we get the Folder object
		$oVersion = $oNewDocument->_oDocumentContentVersion;
		$oConfig =& KTConfig::getSingleton();
		$sDocumentRoot = $oConfig->get('urls/documentRoot');

		$sNewPath = $this->generateStoragePath($oNewDocument);
		$sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $this->generateStoragePath($oSrcDocument));
		$sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);

		$res = KTUtil::copyFile($sFullOldPath, $sFullNewPath);
		if (PEAR::isError($res)) { return $res; }
		$oVersion->setStoragePath($sNewPath);
		$oVersion->update();
     }

	 /**
     * Perform any storage changes necessary to account for a renamed
     * document object.
	 * someone else _must_ call the update on $oDocument
     */
     function renameDocument(&$oDocument, $oOldContentVersion, $sNewFilename) {
        // we get the Folder object
		$oVersion =& $oDocument->_oDocumentContentVersion;
		$oConfig =& KTConfig::getSingleton();
		$sDocumentRoot = $oConfig->get('urls/documentRoot');

		$sOldPath = sprintf("%s/%s-%s", Folder::generateFolderPath($oDocument->getFolderID()), $oOldContentVersion->getId(), $oOldContentVersion->getFileName());
		$sNewPath = sprintf("%s/%s-%s", Folder::generateFolderPath($oDocument->getFolderID()), $oDocument->_oDocumentContentVersion->getId(), $sNewFilename);
		$sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $sOldPath);
		$sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);

		$res = KTUtil::copyFile($sFullOldPath, $sFullNewPath);
		if (PEAR::isError($res)) { return $res; }

		$oVersion->setStoragePath($sNewPath);
		// someone else _must_ call the update.
		return true;		 // RES ?= PEAR::raiseError('.');
     }

	/**
	 * Deletes a document- moves it to the Deleted/ folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */
	function delete($oDocument) {
        $oConfig =& KTConfig::getSingleton();
		$sCurrentPath = $this->getPath($oDocument);

		// check if the deleted folder exists and create it if not
        $sDeletedPrefix = sprintf("%s/Deleted", $oConfig->get('urls/documentRoot'));
		if (!file_exists($sDeletedPrefix)) {
            mkdir($sDeletedPrefix, 0755);
        }

        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sOldPath = $oVersion->getStoragePath();
            $sNewPath = sprintf("Deleted/%s-%s", $oVersion->getId(), $oVersion->getFileName());
            $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $sOldPath);
            $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);
            KTUtil::moveFile($sFullOldPath, $sFullNewPath);
        }
        return true;
	}

	/**
	 * Completely remove a document from the Deleted/ folder
	 *
	 * return boolean true on successful expunge
	 */
	function expunge($oDocument) {
		parent::expunge($oDocument);
        $oConfig =& KTConfig::getSingleton();
		$sCurrentPath = $this->getPath($oDocument);

		// check if the deleted folder exists and create it if not
        $sDeletedPrefix = sprintf("%s/Deleted", $oConfig->get('urls/documentRoot'));
        $sDocumentRoot = $oConfig->get('urls/documentRoot');

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sPath = sprintf("Deleted/%s-%s", $oVersion->getId(), $oVersion->getFileName());
            $sFullPath = sprintf("%s/%s", $sDocumentRoot, $sPath);
            if (file_exists($sFullPath)) {
                unlink($sFullPath);
            }
        }
        return true;
	}

	/**
	 * Completely remove a document version
	 *
	 * return boolean true on successful delete
	 */
	function deleteVersion($oVersion) {
	    $oConfig =& KTConfig::getSingleton();
	    $sDocumentRoot = $oConfig->get('urls/documentRoot');
	    $iContentId = $oVersion->getContentVersionId();
        $oContentVersion = KTDocumentContentVersion::get($iContentId);

	    $sPath = $oContentVersion->getStoragePath();
	    $sFullPath = sprintf("%s/%s", $sDocumentRoot, $sPath);
	    if (file_exists($sFullPath)) {
            unlink($sFullPath);
	    }
	    return true;
	}

	/**
	 * Restore a document from the Deleted/ folder to the specified folder
	 *
	 * return boolean true on successful move, false otherwhise
	 */
	function restore($oDocument) {
        $oConfig =& KTConfig::getSingleton();
		$sCurrentPath = $this->getPath($oDocument);

		// check if the deleted folder exists and create it if not
        $sDeletedPrefix = sprintf("%s/Deleted", $oConfig->get('urls/documentRoot'));
        $sDocumentRoot = $oConfig->get('urls/documentRoot');
	$oNewFolder = Folder::get($oDocument->getFolderID());

        $aVersions = KTDocumentContentVersion::getByDocument($oDocument);
        foreach ($aVersions as $oVersion) {
            $sNewPath = sprintf("%s/%s-%s", KTDocumentCore::_generateFolderPath($oNewFolder->getID()), $oVersion->getId(), $oVersion->getFileName());
	    $oVersion->setStoragePath($sNewPath);
            $sOldPath = sprintf("Deleted/%s-%s", $oVersion->getId(), $oVersion->getFileName());
            $sFullNewPath = sprintf("%s/%s", $sDocumentRoot, $sNewPath);
            $sFullOldPath = sprintf("%s/%s", $sDocumentRoot, $sOldPath);
            KTUtil::moveFile($sFullOldPath, $sFullNewPath);
	    $oVersion->update();

        }
        return true;
	}


	/**
	* View a document using an inline viewer
	*
	* @param 	Primary key of document to view
	*
	* @return int number of bytes read from file on success or false otherwise;
	*
	* @todo investigate possible problem in MSIE 5.5 concerning Content-Disposition header
	*/
	function inlineViewPhysicalDocument($iDocumentID) {
            //get the document
            $oDocument = & Document::get($iDocumentID);
            //get the path to the document on the server
            $sDocumentFileSystemPath = $oDocument->getPath();
            if (file_exists($sDocumentFileSystemPath)) {
                header("Content-Type: application/octet-stream");
                header("Content-Length: ". $oDocument->getFileSize());
                // prefix the filename presented to the browser to preserve the document extension
                header('Content-Disposition: inline; filename="' . $oDocument->getFileName() . '"');
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: must-revalidate");
                header("Content-Location: ".$oDocument->getFileName());
                return readfile($sDocumentFileSystemPath);
            } else {
                return false;
            }
	}

	/**
	* Get the uploaded file information and place it into a document object
	*
	* @param	Array containing uploaded file information (use $aFileArray)
	* par		Primary key of folder into which document will be placed
	*
	* @return Document Document object containing uploaded file information
	*/
	function & createDocumentFromUploadedFile($aFileArray, $iFolderID) {
		//get the uploaded document information and put it into a document object
		$oDocument = new Document($aFileArray['name'], $aFileArray['name'], $aFileArray['size'], $_SESSION["userID"], PhysicalDocumentManager::getMimeTypeID($aFileArray['type'], $aFileArray['name']), $iFolderID);
		return $oDocument;
	}
}

?>
