var s = document.createElement("section");
s.innerHTML = 
'<div id="DocumentPreviewModal" class="modal" tabindex="-1" role="dialog">' +
  '<div class="modal-dialog modal-lg" role="document">' +
  '  <div class="modal-content">' +
  '    <div class="modal-header">' +
  '      <h5 class="modal-title">Document Preview</h5>' +
  '      <button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
  '        <span aria-hidden="true">&times;</span>' +
  '      </button>' +
  '    </div>' +
  '    <div class="modal-body" id="DocumentPreviewContent">xxx' +
  '    </div>' +
  '    <div class="modal-footer">' +
  '      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
  '    </div>' +
  '  </div>' +
  '</div>' +
  '</div>';
  document.body.appendChild(s);

function documentPreview(id){
    $.ajax({
        url : 'plugins/ktstandard/documentpreview/documentPreview.php',
        type : 'POST',
        data : {fDocumentId : id} 
    })
    .done(function(response){
        document.getElementById("DocumentPreviewContent").innerHTML = response;
        $("#DocumentPreviewModal").modal();
    })
    .fail(function(error){
        console.log(error);
    });
}