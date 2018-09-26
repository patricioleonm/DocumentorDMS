    var current_id;
    function editLookup(id){
        if(id == current_id){
            return;
        }
        current_id = id;
        var div = document.getElementById(id);
        var value = div.innerHTML;
        matches = value.match(/"/g);
        var newValue = value;
        if(matches){
            for(var i = 0; i < matches.length; i++){
                newValue = newValue.replace('"', '&#34;');
            }
        }

        var inner = '<input type="text" name="lookup['+id+']" id="lookup_'+id+'" value="'+newValue+'" class="form-control form-control-sm mb-1" />';
        inner += '<input type="hidden" id="original_'+id+'" value="'+newValue+'" />';
        inner += '<input type="submit" name="submit[edit]" value="Save" class="btn btn-primary btn-sm float-right"/>';
        inner += '<input type="button" onclick="javascript: closeLookupEdit('+id+');" name="cancel" value="Cancel" class="btn btn-warning btn-sm float-left"/>';
        div.innerHTML = inner;
        document.getElementById('lookup_'+id).focus();
    }

    function closeLookupEdit(id)
    {
        current_id = 0;
        value = document.getElementById('original_'+id).value;
        document.getElementById(id).innerHTML = value;
    }