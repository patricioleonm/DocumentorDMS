var criterias = new Array();

function addCriteriaGroup(){
    criteria = criterias.length;
    criterias[criteria] = { "fields" : 0 };    
    var t = document.getElementById("tpl_criteria_group");
    var card = document.importNode(t.content, true);
    card.querySelectorAll("select").forEach(x => { x.id = x.id.replace('XX', criteria); x.attributes["data-id"].value = criteria; });
    card.querySelectorAll("div").forEach(x => x.id = x.id.replace('XX', criteria) );
    card.querySelectorAll("button").forEach(x => { x.attributes["data-id"].value = criteria; x.id = x.id.replace('XX', criteria); });
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
    selector2.addEventListener('change', selector2_Change);
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

function selector2_Change(e){

};

function createRow(group){
    var fid = ++criterias[criteria].fields;
    var div = document.createElement("div");
    div.id = "field"+group+"_"+fid;
    div.className = "row mb-1 form-inline";
    return {'div' : div, 'fid' : fid};
};

function createExpr(group, fid, display, value){
    var div = document.createElement("div");
    div.classList.add('col-2');
    div.appendChild(document.createTextNode(display));
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'field' + group + '_' + fid + 'expr';
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

function addCriteria(e){
    var group = e.target.attributes['data-id'].value;
    var selector0 = document.getElementById('selector0_' + group).value;
    
    switch(parseInt(selector0)){
        case 0:
            addMetaField(group);
            break;
        case 1:
            newFields.push({
                field : Object.values(meta_fieldsets).find(x => x.id == selector1.value).fields.find(x => x.id == selector2.value),
                expr : '["'+selector1[selector1.selectedIndex].text+'"]["'+field.name+'"]'
            });
            break;
        case 2:
            newFields.push({
                field : Object.value()
            });
            break;
    };
};

function createMimeTypes(groupid, fid){

};


function getFieldTypeSelection(group,fid,type,options){

	switch(type)
	{
		case 'FILESIZE':
			//html = createFilesize(groupid, fid);
            //return {html: html, init: initFilesize};
            break;
		case 'MIMETYPES':
			return createMimeTypes(group, fid);
			break;
		case 'DOCTYPES':
			//html = createDocTypes(groupid, fid);
			break;
		case 'BOOL':
			//html = createBool(groupid, fid);
			break;
        case 'STRING':
        /*
			if (options != null && options.length != 0)
			{
				html = createSelect(groupid, fid, options);
				break;
            }
            */
			// want to fall through
		case 'FULLTEXT':
        case 'LARGE TEXT':
		case 'STRINGMATCH':
			return createText(group, fid, type);
			break;
		case 'USERLIST':
			//html = createUserList(groupid, fid);
			break;
		case 'DATE':
			//html = createDate(groupid, fid);
            //return {html: html, init: initDate};
            break;
		case 'INT':
			//html = createInt(groupid, fid);
            //return {html: html, init: initInt};
            break;
		case 'FLOAT':
			//html = createFloat(groupid, fid);
            //return {html: html, init: initFloat};
            break;
		case 'DATEDIFF':
			//html = createDateDiff(groupid, fid);
            //return {html: html, init: initDateDiff};
            break;
		default:
			//html += 'unknown: ' + type;
	}
};

function createText(group, fid, type){
    var elements = new Array();

    var div = createFieldDiv();
    var name = "field"+ group + "_" + fid +"op";
    
    switch(type)	{
        case 'STRINGMATCH':
            var select = createSelect(name, objToArray(is_isnot), false);
            select.readonly = true;
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
    div.appendChild(createInput("field"+  group + "_" + fid +"start", "text", null, true));
    elements.push(div);
    return elements;
};

function createFieldDiv(){
    var div = document.createElement("div");
    div.classList.add('col-auto');
    return div;
}

function createSelect(name, options, multiple, callback){
    var select = document.createElement("select");
    select.name = name;
    select.classList.add("form-control","form-control-sm");
    options.forEach(x => select.options.add(new Option(x[0], x[1])) );
    if(multiple){
        select.multiple = true;
        select.size = 8;
    }
    if(callback){
        select.onchange = callback;
    }
    return select;
}

function createInput(name, type, value, readonly){
    var input = document.createElement("input");
    input.type = type;
    input.value = value;
    input.classList.add("form-control","form-control-sm");
    if(readonly != undefined){
        input.readonly = readonly;
    }
    return input;
}

function objToArray(obj){
    return Object.values(obj).map(x => Object.values(x));
}

function removeRow(group, fid){
    console.log(group, fid);
    //e.target.parentNode.parentNode.removeChild(e.target.parentNode);
};