//To hide the link for existing bulk upload link
/*
jQuery(document).ready(function(){ 
	var elems = jQuery("ul.actionlist").find("a");
	for (i = 0; i < elems.length; i++) {
		if(elems[i].href.search("kt_path_info=ktcore.actions.folder.bulkUpload") > -1 || elems[i].href.search("kt_path_info=inetfoldermetadata.actions.folder.bulkUpload") > -1)
		{
			jQuery(elems[i]).parent("li").hide();
		}
	}
});
// added by SL:2009-03-04
jQuery(document).ready(function(){ 
	var elems = jQuery("ul.actionlist").find("a");
	for (i = 0; i < elems.length; i++) {
		if(elems[i].href.search("kt_path_info=ktcore.actions.folder.bulkImport") > -1 || elems[i].href.search("kt_path_info=inetfoldermetadata.actions.folder.bulkUpload") > -1)
		{
			jQuery(elems[i]).parent("li").hide();
		}
	}
});
*/