function getFileName (e){
    var doc = document.getElementById('document_name');
    //if(doc.value == ''){
        var arrPath=this.value.split('/');
        if(arrPath.length == 1){
            var arrPath=this.value.split('\\');
        }
        var name=arrPath[arrPath.length-1];
        var name=name.split('.');
        var len = name.length;
        if(len > 1){
            if(name[len-1].length <= 4){
                name.pop();
            }
        }
        var title=name.join('.');
        doc.value=title;
    //}
};
var fileElement = document.querySelector("input[type=file]","input[id=0]");
if(fileElement != null){ fileElement.addEventListener("change", getFileName); }

var dropElement = document.getElementById("draging_zone");

if(dropElement != null ){

    var dropable = document.getElementById("dropable");
    var counter = 0;
    function dropEnter(e){
        var result = e.dataTransfer.types.filter(x => x == 'application/x-moz-file' || x == 'Files');
        if(result.length != 0){
            e.preventDefault();
            dropable.classList.add("d-block");    
            counter++;
        }        
    };

    function dropEnd(e){
        e.preventDefault();
        counter--;
        if(counter == 0){
            dropable.classList.remove("d-block");
        }
    };

    function dragOver(e){
        var result = e.dataTransfer.types.filter(x => x == 'application/x-moz-file' || x == 'Files');
        if(result.length != 0){
            e.preventDefault();
        }        
    };

    function droped(e){
        e.preventDefault();
        e.stopPropagation();
        dropable.classList.remove("d-block");
        document.getElementById("upload_form").classList.add("d-block");
        fileElement.files = e.dataTransfer.files;
        fileElement.dispatchEvent(new Event("change"));
        counter = 0;
    };
    
    fileElement.addEventListener("change", getFileName);
    dropElement.addEventListener("dragenter", dropEnter);
    dropElement.addEventListener("dragleave", dropEnd);
    dropElement.addEventListener("dragover", dragOver);
    dropElement.addEventListener("drop", droped);
    dropable.addEventListener("droped", droped);

};