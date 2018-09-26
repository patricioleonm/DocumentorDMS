function ping(){
    var url = document.getElementById("download_url_check").value;
    var code = document.getElementById("code").value;
    var download_link = document.getElementById("download-link");
    var download_progress = document.getElementById("download-progress");
    var error_message = document.getElementById("error-message");
    $.ajax({
            url: url,
            type : 'POST',
            data : {'ping' : 'ping', 'code': code}
        })
        .done((response) => {
            if(response == 'wait'){
                setTimeout("ping()", 2000);
                return;
            }
            download_progress.classList.add("d-none");
            download_link.classList.remove("disabled");
        })
        .fail((error) => {
            error_message.classList.remove("d-none");
        });    
};

$(document).ready(() => {
    ping();
});