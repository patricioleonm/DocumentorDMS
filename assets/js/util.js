
//confirm dialog

function confirmMessage(e){
    var msg = e.target.getAttribute('data-deleteMessage');
    var v = confirm(msg);

    if (v == false) {
        if (e.stopPropagation) {
            e.stopPropagation();
            e.preventDefault();
        }
        else if (window.event)
            return false;
    }
    return v;
};

Object.values(document.querySelectorAll(".ktDelete")).forEach(element => {
    element.addEventListener("click", confirmMessage);
});

document.addEventListener("DOMContentLoaded", () => {
    var searchForm = document.getElementById("frmQuickSearch");
    if(searchForm != null){
        searchForm.addEventListener("submit", (e) => {
            var value = e.target.querySelector("input[type=search]").value;
            if(value == ""){
                e.preventDefault();
                e.stopPropagation();
            }else{
                e.target.querySelector("input[name=txtQuery]").value = '(GeneralText contains "'+value+'")';
            }            
        });
    };
});

var newwindowlinks = document.querySelectorAll(".newwindow");

Object.values(newwindowlinks).forEach(x => x.addEventListener("click", openInNewWindow));

function openInNewWindow(e){
    e.preventDefault();
    window.open(e.target.href,"_blank","toolbar=no,resizable=yes");
}