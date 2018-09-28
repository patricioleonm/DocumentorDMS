var criterias = new Array();

function addCriteriaGroup(){
    criteria = criterias.length;
    criterias[criteria] = { "fields" : 0 };    
    var t = document.getElementById("tpl_criteria_group");
    var card = document.importNode(t.content, true);
    card.querySelectorAll("select").forEach(x => { x.id = x.id.replace('XX', criteria); x.attributes["data-id"].value = criteria; });
    card.querySelectorAll("div[id^=fieldset_],div[id^=criteria_fields]").forEach(x => { x.id = x.id.replace('XX', criteria); x.attributes["data-id"].value = criteria; } );
    card.querySelectorAll("button").forEach(x => { x.attributes["data-id"].value = criteria; x.id = x.id.replace('XX', criteria); });
    if(criteria == 0){
        card.querySelector("#removeCriteriaGroup").classList.add("d-none");
    }    
    document.getElementById("criterias_groups_list").appendChild(card);
    initializeCriteriaGroup(criteria);    
};

function initializeCriteriaGroup(criteria){
    var selector0 = document.getElementById('selector0_' + criteria);
    var selector1 = document.getElementById('selector1_' + criteria);
    var selector2 = document.getElementById('selector2_' + criteria);
    var btn = document.getElementById('btnAdd_' + criteria);
    selector0.addEventListener('change', selector0_Change);
    selector1.addEventListener('change', selector1_Change);
    btn.addEventListener('click', addCriteria);
    selector0.dispatchEvent(new Event('change'));
};

function populateSelect(target, options){
    Object.keys(target.options).forEach(x => target.options.remove(0));
    options.forEach(x => target.options.add(new Option(x[0], x[1])));
    target.dispatchEvent(new Event('change'));
};

function selector0_Change(e){
    var selected = e.target.value;
    var criteria = e.target.attributes["data-id"].value;
    var select = document.getElementById('selector1_' + criteria);
    switch (parseInt(selected)){
        case 0:            
            var options = Object.values(meta_fields).map(x => new Array(x.name, x.alias) );
            document.getElementById('selector2_'+criteria).disabled = true;
            break;
        case 1:
            var options = Object.values(meta_fieldsets).map(x => new Array(x.name, x.id) );
            document.getElementById('selector2_'+criteria).disabled = false;
            break;
        case 2:
            var options = Object.values(meta_workflows).map(x => new Array(x.name, x.id) );
            document.getElementById('selector2_'+criteria).disabled = false;
            break;
    };
    populateSelect(select, options);
};

function selector1_Change(e){
    var criteria = e.target.attributes['data-id'].value;
    var selected0 = parseInt(document.getElementById('selector0_'+criteria).value);
    var selected1 = e.target.value;
    var selected2 = document.getElementById('selector2_'+criteria);
    var options = new Array();
    switch(selected0){
        case 0:
            return;
            break;
        case 1:
            options = Object.values(meta_fieldsets).find(x => x.id == selected1).fields.map(x => new Array(x.name, x.id) );
            break;
        case 2:
            options = Object.values(meta_workflows).find(x => x.id == selected1).states.map(x => new Array(x.name, x.id) );
            break;
    };
    populateSelect(selected2, options);
};

function createRow(group){
    var fid = ++criterias[criteria].fields;
    var div = document.createElement("div");
    div.id = "field"+group+"_"+fid;
    var data_id = document.createAttribute("data-id");
    data_id.value = fid;
    div.setAttributeNode(data_id);
    div.className = "form-row mb-1 form-inline";
    return {'div' : div, 'fid' : fid};
};

function createExpr(group, fid, display, value){
    var div = document.createElement("div");
    div.classList.add('col-2');
    div.appendChild(document.createTextNode(display));
    var input = document.createElement('input');
    input.type = 'hidden';
    input.id = 'field' + group + '_' + fid + 'expr';
    input.value = value;
    div.appendChild(input);
    return div;
};

function addRow(group, fid,  element){
    var target = document.getElementById('criteria_fields' + group);
    element.appendChild(createRemoveButton(group, fid));
    if(target.childNodes.length == 0){
        target.appendChild(element);
    }else{
        target.insertBefore(element, target.firstChild);
    }    
};

function createRemoveButton(group, fid){
    var div = document.createElement("div");
    div.classList.add('col');
    var button = document.createElement('button');
    button.classList.add('btn','btn-danger','btn-sm','float-right');
    button.innerText = remove;
    button.onclick = () => { removeRow(group, fid); };
    div.appendChild(button);
    return div;
}

function addMetaField(group){
    var selector1 = document.getElementById('selector1_'+group);
    var field = Object.values(meta_fields).find(x => x.alias == selector1.value);
    var row = createRow(group);
    row.div.appendChild(createExpr(group, row.fid, field.name, field.alias));    
    var fields = getFieldTypeSelection(group,row.fid,field.type);
    Object.values(fields).forEach(x => row.div.appendChild(x));    
    addRow(group, row.fid, row.div);
};

function addWorkFlowField(group){
    var selector1 = document.getElementById('selector1_'+group);
    var selector2 = document.getElementById('selector2_'+group);

    //add state
    row = createRow(group);    
    row.div.appendChild(createExpr(group, row.fid, "Workflow State","Workflow State"));
    var div = createFieldDiv();
    div.appendChild(createSelect("field"+group+"_"+row.fid+"op", objToArray(is_isnot),false,null));
    row.div.appendChild(div);
    div = createFieldDiv();
    div.appendChild(createInput("field"+group+"_"+row.fid+"start","text",selector2[selector2.selectedIndex].text,true));
    row.div.appendChild(div);
    addRow(group, row.fid, row.div);
    //add workflow
    var row = createRow(group);    
    row.div.appendChild(createExpr(group, row.fid, "Workflow","Workflow"));
    var div = createFieldDiv();
    div.appendChild(createSelect("field"+group+"_"+row.fid+"op", objToArray(is_isnot),false,null));
    row.div.appendChild(div);
    div = createFieldDiv();
    div.appendChild(createInput("field"+group+"_"+row.fid+"start","text",selector1[selector1.selectedIndex].text,true));
    row.div.appendChild(div);
    addRow(group, row.fid, row.div);
};

function addFieldSetFields(group){
    var selector1 = document.getElementById('selector1_'+group);
    var selector2 = document.getElementById('selector2_'+group);
    var field = Object.values(meta_fieldsets).find(x => x.id == selector1.value).fields.find(x => x.id == selector2.value);

    var row = createRow(group);
    row.div.appendChild(createExpr(group, row.fid, selector1[selector1.selectedIndex].text+"/"+field.name, '["'+selector1[selector1.selectedIndex].text+'"]["'+field.name+'"]'));
    
    var fields = getFieldTypeSelection(group,row.fid,field.datatype, field);

    Object.values(fields).forEach(x => row.div.appendChild(x));
    
    addRow(group, row.fid, row.div);
};

function addCriteria(e){
    var group = e.target.attributes['data-id'].value;
    var selector0 = document.getElementById('selector0_' + group).value;    
    switch(parseInt(selector0)){
        case 0:
            addMetaField(group);
            break;
        case 1:
            addFieldSetFields(group);
            break;
        case 2:
            addWorkFlowField(group);
            break;
    };
};

function getFieldTypeSelection(group,fid,type,options){
	switch(type)
	{
		case 'FILESIZE':
			return createFilesize(group, fid);
            break;
		case 'MIMETYPES':
			return createMimeTypes(group, fid);
			break;
		case 'DOCTYPES':
			return createDocTypes(group, fid);
			break;
		case 'BOOL':
			return createBool(group, fid);
			break;
        case 'STRING':
            if(options != undefined && options.options != undefined && options.options != undefined && options.options.length > 0){
                return createSelectFieldset(group, fid, options);
            }
		case 'FULLTEXT':
        case 'LARGE TEXT':
		case 'STRINGMATCH':
			return createText(group, fid, type);
		case 'USERLIST':
			return createUserList(group, fid);
        case 'DATE':
            return createRange(group, fid, 'DATE');
            break;
        case 'INT':
            return createRange(group, fid, 'INT');
            break;
        case 'FLOAT':
            return createRange(group, fid, 'FLOAT');
            break;
		case 'DATEDIFF':
			return createDateDiff(group, fid);
            break;
		default:
			return new Array(document.createTextNode('unknown: ' + type));
	}
};

function createSelectFieldset(group, fid, options){
    var elements = new Array();
    var div = createFieldDiv();
    if(options.control == "inetlookup"){
        div.appendChild(createSelect("field"+group+"_"+fid+"op",objToArray(contains_notcontains2),false,null));
        elements.push(div);
        div = createFieldDiv();
        div.appendChild(createSelect("field"+ group + "_" + fid +"join",objToArray(and_or),false,null));
        elements.push(div);
        if(options.inetlookup_type == "multiwithlist"){
            div = createFieldDiv();
            var select = createSelect("field"+ group + "_" + fid +"start[]",Object.values(options.options).map(x => new Array(x.name, x.id)),true,null);
            select.classList.add("inetlookup");
            div.appendChild(select);
            elements.push(div);
        }else if(options.inetlookup_type == "multiwithcheckboxes"){
            div = createFieldDiv();
            div.style.height = "5em";
            div.style.overflow = "auto";
            div.classList.add("col-2");
            Object.values(options.options).forEach(x => 
            {
                var label = document.createElement("label");  
                var checkbox = document.createElement("input");
                var span = document.createElement("span");
                checkbox.type = "checkbox";
                checkbox.value = x.name;
                checkbox.id = "field"+ group + "_" + fid +"start[]";
                checkbox.classList.add("inetlookup");
                span.innerText = x.name;  
                label.appendChild(checkbox);
                label.appendChild(span);
                div.appendChild(label);
            });
            elements.push(div);
        }
    }else{
        div.appendChild(createSelect("field"+group+"_"+fid+"op",objToArray(is_isnot),false,null));
        elements.push(div);
        div = createFieldDiv();
        div.appendChild(createSelect("field"+ group + "_" + fid +"start",Object.values(options.options).map(x => new Array(x.name, x.id)),false,null));
        elements.push(div);
    }
    return elements;
}

function createText(group, fid, type){
    var elements = new Array();
    var div = createFieldDiv();
    var name = "field"+ group + "_" + fid +"op";
    var readonly = false;
    switch(type)	{
        case 'STRINGMATCH':
            var select = createSelect(name, objToArray(is_isnot), false);
            readonly = true;
			break;
		case 'STRING':
        case 'LARGE TEXT':
            var select = createSelect(name, objToArray(conditions3), false);
			break;
		case 'FULLTEXT':
            var select = createSelect(name, objToArray(contains_notcontains), false);
			break;
    }
    div.appendChild(select);
    elements.push(div);
    div = createFieldDiv();
    div.appendChild(createInput("field"+  group + "_" + fid +"start", "text", null, readonly));
    elements.push(div);
    return elements;
};

function createUserList(group, fid){
    var elements = new Array();
    var div = createFieldDiv();
    var name = "field" + group + "_" + fid + "op";
    div.appendChild(createSelect(name, objToArray(is_isnot),false,null));
    elements.push(div);
    div = createFieldDiv();
    div.appendChild(createSelect("field" + group + "_" + fid + "start", Object.values(meta_users).map(x => new Array(x.name, x.name) ), false, null));
    elements.push(div);
    return elements;
};

function createBool(group, fid){
    var elements = new Array();
    var div = createFieldDiv();
    div.appendChild(createSelect("field"+ group + "_" + fid +"op", objToArray(is_isnot) ,false, null));
    elements.push(div);
    div = createFieldDiv();
    div.appendChild(createSelect("field"+ group + "_" + fid +"start", objToArray(true_false),false,null));
    elements.push(div)
    return elements;
};

function createDateDiff(group, fid){
    var elements = new Array();
    var div = createFieldDiv();
    div.appendChild(createSelect("field"+ group + "_" + fid +"op", objToArray(less_greater) ,false,null));
    elements.push(div);
    div = createFieldDiv();
    var input = createInput("field"+ group + "_" + fid +"input","number",1,false);
    input.addEventListener("change", onDateDiffChange(group,fid));
    div.appendChild(input);
    elements.push(div);
    div = createFieldDiv();
    div.appendChild(createSelect("field" + group + "_" + fid + "period", objToArray(times),false, onDateDiffChange(group,fid)))
    elements.push(div);
    elements.push(createInput("field" + group + "_" + fid + "start","hidden",1,false));
    return elements;
};

function createFilesize(group, fid){
    var elements = new Array();
    var div = createFieldDiv();
    div.appendChild(createSelect("field"+ group + "_" + fid +"op", objToArray(less_greater) ,false, null))
    elements.push(div);
    div = createFieldDiv();
    var input = createInput("field"+ group + "_" + fid +"input","number",1,false);
    input.addEventListener("change", onFilesizeChange(group, fid));
    div.appendChild(input);
    elements.push(div);
    div = createFieldDiv();
    div.appendChild(createSelect("field" + group + "_" + fid + "factor", objToArray(filesizes),false,onFilesizeChange(group, fid)))
    elements.push(div);
    elements.push(createInput("field" + group + "_" + fid + "start","hidden", 1, false));
    return elements;
};

function createDocTypes(group, fid){
    var elements = new Array();
    var div = createFieldDiv();
    div.appendChild(createSelect("field"+ group + "_" + fid +"op", objToArray(is_isnot) ,false, null));
    elements.push(div);
    div = createFieldDiv();
    div.appendChild(createSelect("field"+ group + "_" + fid +"start", Object.values(meta_documenttypes).map(x => new Array(x.name, x.name)) ,false, null))
    elements.push(div);
    return elements;
};

function createRange(group, fid, type){
    var elements = new Array();
    var div = createFieldDiv();
    var select = null;
    //callback pending
    switch(type){
        case 'DATE':
            select = createSelect("field" + group + "_" + fid + "op",objToArray(conditions1),false, null);
            break;
        default:
            select = createSelect("field" + group + "_" + fid + "op",objToArray(conditions2),false, null);
            break;
    }
    select.addEventListener("change", onBetweenChange(group, fid, select));
    div.appendChild(select);
    elements.push(div);
    div = createFieldDiv();
    div.appendChild(createInput("field"+ group + "_" + fid +"start",type,"",false));
    elements.push(div);
    div = createFieldDiv();
    div.classList.add("range"+group+"_"+fid,"d-none");
    div.appendChild(document.createTextNode(" AND "))
    elements.push(div);
    div = createFieldDiv();
    div.classList.add("range"+group+"_"+fid,"d-none");
    div.appendChild(createInput("field"+ group + "_" + fid + "end", type,"",false));
    elements.push(div);
    return elements;
};

function createFieldDiv(){
    var div = document.createElement("div");
    div.classList.add('col-auto');
    return div;
};

function createSelect(name, options, multiple, callback){
    var select = document.createElement("select");
    select.id = name;
    select.classList.add("form-control","form-control-sm");
    options.forEach(x => select.options.add(new Option(x[0], x[1])) );
    if(multiple){
        select.multiple = true;
        select.size = 4;
    }
    if(callback){
        select.addEventListener("change", callback);
    }
    return select;
};

function createInput(name, type, value, readonly){
    var input = document.createElement("input");
    input.type = (type == 'INT' || type == 'FLOAT') ? 'number' : type;
    input.value = value;
    input.classList.add("form-control","form-control-sm");
    input.id = name;
    if(readonly == true){
        input.readOnly = true;
    }
    return input;
};

function objToArray(obj){
    return Object.values(obj).map(x => Object.keys(x).map(y =>  new Array(x[y], y))).flat();
};

function removeRow(group, fid){
    var div = document.getElementById("field"+group+"_"+fid);
    div.parentNode.removeChild(div);
};

function removeCriteriaGroup(e){
    var div = document.getElementById("fieldset_"+e.attributes["data-id"].value);
    div.parentNode.removeChild(div);
};

document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("btnAddCriteria").dispatchEvent(new Event("click"));
});

function butSearchClick(){
    var expr = buildExpression();
    console.log(expr);
	if (expr == '')
	{
		alert(select_criteria);
		return;
	}
	var txtQuery = document.getElementById("txtQuery");
	txtQuery.value=expr;
	var frm = document.getElementById('frmQuickSearch');
	frm.submit();
};

function getInetvalues(groupid, field_id){
    var checkvalues = document.querySelectorAll("input[id^=field"+groupid+"_"+field_id+"start]");
    var selectvalues = document.querySelector("select[id^=field"+groupid+"_"+field_id+"start]");

    if(checkvalues == undefined && selectvalues == undefined){
        return [];
    }
    var values = null;
    if(selectvalues != null){
        values = Object.values(selectvalues.options).filter(x => x.selected == true).map(x => x.text);
    }else{
        values = Object.values(checkvalues).filter(x => x.checked).map(x => x.value);
    }
    return values;
};

function buildExpression(){
    var mainOp = document.getElementById('allop').value;
    var main_str = "";
    var group_query = new Array();
    document.querySelectorAll("div[id^=fieldset_]").forEach((d_group) => {
        var groupid = d_group.attributes["data-id"].value;
        var criteriaOp = document.getElementById("groupop_"+groupid).value;
        var field_query = new Array();
        document.getElementById("criteria_fields0").childNodes.forEach((field) => {
            var field_id = field.attributes["data-id"].value;
            var field_str = "";
            //build query
            field_str = document.getElementById("field"+groupid+"_"+field_id+"expr").value;
            var fieldOp = document.getElementById("field"+groupid+"_"+field_id+"op").value;
            field_str +=" "+fieldOp;            
            var join = document.getElementById("field"+groupid+"_"+field_id+"join");
            if(join == undefined){
                field_str += " \"" + document.getElementById("field"+groupid+"_"+field_id+"start").value+"\"";
                if(fieldOp.toUpperCase() == "BETWEEN"){
                    field_str += " AND ";
                    field_str += "\"" + document.getElementById("field"+groupid+"_"+field_id+"end").value+"\"";
                }
                field_query.push(field_str);
            }else{ //inetlookup fields
                var values = getInetvalues(groupid, field_id);
                if(values.length > 0){                                
                    var inet_query = new Array();
                    values.forEach(x => {
                        inet_query.push(field_str + " \""+ x +"\"");
                    });
                    field_query.push("(" + inet_query.join(" "+join.value+" ") +")");
                };
            }
        });
        if(field_query.length > 0){
            group_query.push("("+field_query.join(" "+criteriaOp+" ")+")");
        };
    });
    main_str = (group_query.length > 0) ? group_query.join(" "+mainOp+" ") : "";
	return main_str;
};

function onDateDiffChange(groupid, fid){
    return function(){
        var prefix = 'field' + groupid + '_' + fid;
        var period=1;
        switch(document.getElementById(prefix + 'period').selectedIndex)
        {
            case 1: period = 30; break;
            case 2: period = 365; break;
        }

        document.getElementById(prefix + 'start').value = parseInt(document.getElementById(prefix + 'input').value) * period;
    };
};

function onFilesizeChange(groupid, fid){
    return function(){
        var prefix = 'field' + groupid + '_' + fid;
        var factor=1;
        switch(document.getElementById(prefix + 'factor').selectedIndex)
        {
            case 1: factor = 1024; break;
            case 2: factor = 1024*1024; break;
            case 3: factor = 1024*1024*1024; break;
        }
        document.getElementById(prefix + 'start').value = parseInt(document.getElementById(prefix + 'input').value) * factor;
    };
};

function onBetweenChange(groupid, fid, element)
{
    return function(){
        var op = element[element.selectedIndex].value.toUpperCase();
        var divs = document.querySelectorAll(".range"+groupid+"_"+fid);
        switch (op){
            case 'BETWEEN':
                Object.values(document.querySelectorAll(".range"+groupid+"_"+fid)).forEach(x => x.classList.add('d-block'));
                break;
            default:
                Object.values(document.querySelectorAll(".range"+groupid+"_"+fid)).forEach(x => x.classList.remove('d-block'));
                break;
        };
    };
};