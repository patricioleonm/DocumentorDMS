function kt_add_document_addMessage(message) {
    var my_div = document.getElementById('kt-add-document-target');
    my_div.innerHTML += "<pre>" + message + "</pre>";
}

function kt_add_document_newFile(filename) {
    var my_div = document.getElementById('kt-add-document-target');
    my_div.innerHTML += "<pre>" + filename + "</pre>";
}

function kt_add_document_redirectToDocument(id) {
    var base = document.getElementsByName('kt-core-baseurl')[0].value;
    var href = base + "/control.php?action=viewDocument&fDocumentId=" + id;
    document.location.href = href;
}

function kt_add_document_redirectToFolder(id) {
    var base = document.getElementsByName('kt-core-baseurl')[0].value;
    document.location.href = base + "/control.php?action=browse&fFolderId=" + id;
}

function kt_add_document_redirectTo(url) {
    document.location.href = url;
}
