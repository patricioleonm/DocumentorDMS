function updateMetadata(select, target){
    this._select = select;
    this._target = target;

    this.swapInItem = (metadata) => {
        var cp = document.getElementById(this._target);
        cp.innerHTML = metadata;        
        //initialiseConditionalFieldsets();
    }
    
    this.getDataForType = (id) => {
        this.swapInItem("<i class=\"fas fa-circle-notch fa-spin fa-3x\"></i>");
        return new Promise((resolve, reject) => {
            $.ajax({
                url : 'presentation/lookAndFeel/knowledgeTree/documentmanagement/getTypeMetadataFields.php?fDocumentTypeID=' + id,
                type : 'GET'
            })
            .done((data) => {
                resolve(data);
            })
            .fail((error) => {
                reject(error);
            });
        });    
    }
    
    
    this.document_type_changed = () => {
        typeselect = document.getElementById(this._select);
        this.getDataForType(typeselect.value)
        .then((data) => {
            this.swapInItem(data);
        },
        (error) => {
            alert("Error : " + error);
        });
    }
    
    this.start = () => {
        var typeselect = document.getElementById(this._select);
        typeselect.addEventListener("change", this.document_type_changed);
        this.document_type_changed();
    }
    return {
        start : this.start
    };
};

window.onload = () => {
    var u = new updateMetadata('add-document-type','type_metadata_fields');
    u.start();
};
