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

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

require_once(KT_DIR . '/plugins/pdfConverter/pdfConverter.php');

class PDFGeneratorAction extends KTDocumentAction {
    var $sName = 'ktstandard.pdf.generate';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = 'Generate PDF';
    // Note: 'asc' below seems to be a catchall for plain text docs.
    //       'htm' and 'html' should work but are not so have been removed for now.
    var $aAcceptedMimeTypes = array('doc', 'ods', 'odt', 'ott', 'txt', 'rtf', 'sxw', 'stw',
            //                                    'html', 'htm',
            'xml' , 'pdb', 'psw', 'ods', 'ots', 'sxc',
            'stc', 'dif', 'dbf', 'xls', 'xlt', 'slk', 'csv', 'pxl',
            'odp', 'otp', 'sxi', 'sti', 'ppt', 'pot', 'sxd', 'odg',
            'otg', 'std', 'asc');

    function getDisplayName() {
        global $default;
        // The generation of the pdf is done through the PDF Converter plugin.
        // The PDF's are generated in the background by the document processor

        if(!empty($this->oDocument)){
            $iDocId = $this->oDocument->iId;

            // Build the display name and url
            $sDisplayName = _kt('Generate PDF');

            $sHostPath = KTUtil::kt_url();
            //$link = KTUtil::ktLink('action.php', 'ktstandard.pdf.generate', array( 'fDocumentId' => $this->oDocument->getId(), 'action' => 'pdfdownload'));

            // First check if the pdf has already been generated
            $dir = $default->pdfDirectory;
            $file = $dir .'/'. $iDocId . '.pdf';

            if(file_exists($file)){
                return $sDisplayName;
            }

            // If the file does not exist, check if the document has the correct mimetype
            $converter = new pdfConverter();
            $mimeTypes = $converter->getSupportedMimeTypes();
            $docType = $this->getMimeExtension();

            if($mimeTypes === true || in_array($docType, $mimeTypes)){
                // Display the download link
                return $sDisplayName;
            }
        }else{
            // If the document is empty then we are probably in the workflow admin - action restrictions section, so we can display the name.
            return $sDisplayName;
        }

        return '';
    }

    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
                    'label' => _kt('Convert Document to PDF'),
                    'action' => 'selectType',
                    'fail_action' => 'main',
                    'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
                    'submit_label' => _kt('Convert Document'),
                    'context' => &$this,
                    ));

        $oForm->setWidgets(array(
                    array('ktcore.widgets.selection', array(
                            'label' => _kt("Type of conversion"),
                            'description' => _kt('The following are the types of conversions you can perform on this document.'),
                            //'important_description' => _kt('QA NOTE: Permissions checks are required here...'),
                            'name' => 'convert_type',
                            //'vocab' => array('Download as PDF', 'Duplicate as PDF', 'Replace as PDF'),
                            'vocab' => array('Download as PDF'),
                            'simple_select' => true,
                            'required' => true,
                            )),
                    ));

        return $oForm;
    }

    function do_selectType() {

        switch($_REQUEST[data][convert_type]){
            case '0':
                $this->do_pdfdownload();
                break;
            case '1':
                $this->do_pdfduplicate();
                break;
            case '2':
                $this->do_pdfreplace();
                break;
            default:
                $this->do_pdfdownload();
        }
        redirect(KTUtil::ktLink( 'action.php', 'ktstandard.pdf.generate', array( "fDocumentId" => $this->oDocument->getId() ) ) );
        exit(0);
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        return $oTemplate->render();
    }

    /**
     * Method for getting the MIME type extension for the current document.
     *
     * @return string mime time extension
     */
    function getMimeExtension() {

        if($this->oDocument == null || $this->oDocument == "" || PEAR::isError($this->oDocument) ) return _kt('Unknown Type');

        $oDocument = $this->oDocument;
        $iMimeTypeId = $oDocument->getMimeTypeID();
        $mimetypename = KTMime::getMimeTypeName($iMimeTypeId); // mime type name

        // the pdf converter uses the mime type and not the extension.
        return $mimetypename;

        /*
        $sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT filetypes FROM " . $sTable . " WHERE mimetypes = ?";
        $aQuery = array($sQuery, array($mimetypename));
        $res = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        } else if (count($res) != 0){
            return $res[0]['filetypes'];
        }

        return _kt('Unknown Type');
        */
    }

    /**
     * Method to download the pdf.
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public function do_pdfdownload()
    {
        global $default;
        $iDocId = $this->oDocument->iId;

        // Check if pdf has already been created
        $dir = $default->pdfDirectory;
        $file = $dir .'/'. $iDocId . '.pdf';
        $mimetype = 'application/pdf';
        $size = filesize($file);

        // Set the filename
        $name = $this->oDocument->getFileName();
        $aName = explode('.', $name);
        array_pop($aName);
        $name = implode('.', $aName) . '.pdf';


        if(file_exists($file)){
            if(KTUtil::download($file, $mimetype, $size, $name) === false){
                $default->log->error('PDF Generator: PDF file could not be downloaded because it doesn\'t exist');
                $this->errorRedirectToMain(_kt('PDF file could not be downloaded because it doesn\'t exist'));
            }
            exit();
        }

        // If not - create one
        $converter = new pdfConverter();
        $converter->setDocument($this->oDocument);
        $res = $converter->processDocument();

        if($res !== true){
            // please contact your System Administrator
            $default->log->error('PDF Generator: PDF file could not be generated');
            $this->errorRedirectToMain($res . ' ' . _kt('Please contact your System Administrator for assistance.'));
            exit();
        }

        if(file_exists($file)){
            if(KTUtil::download($file, $mimetype, $size, $name) === false){
                $default->log->error('PDF Generator: PDF file could not be downloaded because it doesn\'t exist');
                $this->errorRedirectToMain(_kt('PDF file could not be downloaded because it doesn\'t exist'));
            }
            exit();
        }
        $this->errorRedirectToMain($error);
        exit();
    }

    public function do_showpdf(){
		global $default;

        $mimeTypeId = $this->oDocument->getMimeTypeID();
        $mimeType = KTMime::getMimeTypeName($mimeTypeId);

	    // Get the pdf source file - if the document is a pdf then use the document as the source
	    if($mimeType == 'application/pdf') {
	        $pdfDir = $default->documentRoot;
            $pdfFile = $pdfDir . DIRECTORY_SEPARATOR . $this->oDocument->getStoragePath();
	    } else {
    	    $pdfDir = $default->pdfDirectory;
            $pdfFile = $pdfDir .DIRECTORY_SEPARATOR. $this->oDocument->getId().'.pdf';
	    }
        // Set the filename
        $name = $this->oDocument->getFileName();
        $aName = explode('.', $name);
        array_pop($aName);
        $name = implode('.', $aName) . '.pdf';

        header('Content-type:application/pdf');
        header('Content-disposition: inline; filename="'.$name.'"');
        header('content-Transfer-Encoding:binary');
        header('Accept-Ranges:bytes');
        @readfile($pdfFile);
        exit(0);
    }

    /**
     * Method for duplicating the document as a pdf.
     *
     */
    function do_pdfduplicate() {

        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        $this->addErrorMessage(_kt('NOT IMPLEMENTED YET: This will create a pdf copy of the document as a new document.'));
        return $oTemplate->render();

    }

    /**
     * Method for replacing the document as a pdf.
     *
     */
    function do_pdfreplace() {

        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        $this->addErrorMessage(_kt('NOT IMPLEMENTED YET: This will replace the document with a pdf copy of the document.'));
        return $oTemplate->render();

    }
}
?>
