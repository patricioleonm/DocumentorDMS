
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