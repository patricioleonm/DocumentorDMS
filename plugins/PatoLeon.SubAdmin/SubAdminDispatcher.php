<?php
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/plugins/KTAdminNavigation.php');

class PatoLeonSubAdminDispatcher extends KTAdminDispatcher {
    
	function predispatch() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Sub Admin'));
    }

	function do_update(){
		$helpers = $_POST['aHelpers'];
		//eliminar anteriores
		 mysql_query("DELETE FROM subadmin_helper");
		//recorre array e insertar nuevos
		foreach($helpers as $helper){
			mysql_query("INSERT INTO subadmin_helper(namespace) values('".$helper."')");
		}
		return $this->successRedirectToMain(_kt("Item updated"));
	}
	
    function do_main () {
		
		//$this->oPage->title = _kt('Assign SubAdmin Helpers');
		
		// are we categorised, or not?
        $oRegistry =& KTAdminNavigationRegistry::getSingleton();
		$categories = $oRegistry->getCategories();
		$aAllItems = array();
		
        // we need to investigate sub_url solutions.
        foreach ($categories as $aCategory) {
			$aItems = $oRegistry->getItemsForCategory($aCategory['name']);
			$category['title'] = $aCategory['title'];
			$category["description"] = $aCategory["description"];
			$items = array();
			foreach($aItems as $item){
				$items[] = array("title" => $item["title"], "description" => $item["description"], "fullname" => $item["fullname"]);
			}
			$category['items'] = $items;
			$aAllItems[] = $category;
		}

        //get autorized helpers
		$sql = "SELECT namespace
				FROM subadmin_helper";

		$result = DBUtil::getResultArray($sql);

		foreach($result as $row){
			$assigned_helpers[] = $row["namespace"];
		}
		
		$oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('sub_admin');
        $aTemplateData = array(
              'context' => $this,
              //'categories' => $categories,
              'all_items' => $aAllItems,
			  'assigned_helpers' => $assigned_helpers,
              'baseurl' => $_SERVER['PHP_SELF'],
        );
        return $oTemplate->render($aTemplateData);
	}
}
