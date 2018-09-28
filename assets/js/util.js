
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

