
//confirm dialog
/*
document.addEventListener('click', (e) => {
    console.log("click");
    if(e.target.classList.contains('ktDelete')){
        var msg = e.target.getAttribute('kt:deleteMessage');
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
    }
});
*/

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

var bdsidebar = document.getElementById("bd-sidebar");
var bdcontent = document.getElementById("bd-content");
var btnshowsidebar = document.getElementById("btn-show-sidebar");
var btnhidesidebar = document.getElementById("btn-hide-sidebar");

if(btnshowsidebar != undefined){
    btnshowsidebar.addEventListener("click", (e) => {
        bdsidebar.classList.remove("d-sm-none");
        bdsidebar.classList.add("d-block");
        $(btnshowsidebar).hide();
        $(btnhidesidebar).show();
    });
}

if(btnhidesidebar != undefined){
    btnhidesidebar.addEventListener("click", (e) => {
        bdsidebar.classList.remove("d-block");
        bdsidebar.classList.add("d-sm-none");
        $(btnhidesidebar).hide();
        $(btnshowsidebar).show();
    });
}