var formats = {
    "document" : ["doc", "docx", "odt", "rft"],
    "spreadsheet" : ["xls",  "xlsx", "ods"],
    "presentation" : ["ppt", "pptx", "odp"]
};

function chooseParameters(e){
    e.preventDefault();
    e.stopPropagation();

    document.getElementById("modal_filename").classList.remove("is-invalid");
    var modal = document.getElementById("name_format");
    var format_field = document.getElementById("format_field");

    var type = e.currentTarget.attributes["data-type"].value;

    document.getElementsByName("type")[0].value = type;
    var template_id = document.getElementsByName("template_id")[0];
    switch(type){
        case 'new':
            var format = e.currentTarget.attributes["data-format"].value;
            var format_select = document.getElementById("modal_select_format");
            Object.values(format_select.options).forEach(x => format_select.options[0].remove());
            formats[format].forEach(x => format_select.options.add(new Option(x)));
            format_field.classList.remove("d-none");
            format_field.classList.add("d-block");
            break;
        case 'template':
            format_field.classList.remove("d-block");
            format_field.classList.add("d-none");
            template_id.value = e.currentTarget.attributes["data-template-id"].value;
            break;
        case 'merge':
            format_field.classList.remove("d-block");
            format_field.classList.add("d-none");
            template_id.value = e.currentTarget.attributes["data-template-id"].value;
            break;
    }

    $("#name_format").modal({ keyboard : false, backdrop : 'static ' });
}

function validate(){
    var modal_filename = document.getElementById("modal_filename");
    if(modal_filename.value == "" || modal_filename.value == undefined){
        modal_filename.classList.add("is-invalid");
        return;
    }
    document.getElementsByName("filename")[0].value = modal_filename.value;
    document.getElementsByName("format")[0].value = document.getElementById("modal_select_format").value;
    document.getElementsByName("doctype_id")[0].value = document.getElementById("modal_doctype").value;

    document.getElementById("main_form").submit();
}

document.getElementById("nav-tabContent").querySelectorAll("a").forEach(x => x.addEventListener("click", chooseParameters));
