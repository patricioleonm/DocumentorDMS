function ManagePermissions(){
    this.selected_entities = {};
    this.entities = {};
    this.permissions = [];

    this.getData = (url) => {
        return new Promise((resolve, reject) =>{
            $.ajax({
                url : url,
                type : 'GET'
            })
            .done((data) => {
                resolve(JSON.parse(data));
            })
            .fail((error) => {
                reject('Error : ' + error);
            });
        });
        
    };

    this.populate_entities = () => {
        var groups = {};
        Object.entries(this.entities).forEach(entity => {
            if(!Object.keys(groups).includes(entity[1].type)){
                var group = document.createElement('optgroup');
                group.label = entity[1].type;
                groups[entity[1].type] = group;
            }
            var option = new Option(entity[1].name, entity[0]);
            groups[entity[1].type].appendChild(option);
            if(entity[1].permissions.length > 0){
                this.selected_entities[entity[0]] = entity[1];
            }
        });

        Object.values(groups).forEach(group => {
            document.update_permissions_form.entities.add(group);
        });
    };

    this.drawTable = () => {
        Object.entries(this.selected_entities).forEach(entity => {
            this.drawRow(entity);
        });
    };
    
    this.drawRow = (rowData) => {
        var table = document.getElementById("permissions-table");
        var row = document.getElementById("perm_row");
        var new_row = document.importNode(row.content,true);
        
        var td = new_row.querySelectorAll("td");
        td[0].textContent = rowData[1].display;
        this.permissions.forEach(permission => {
            var checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.name = "foo["+permission.id+"]["+rowData[1].type+"][]";
            checkbox.value = rowData[1].id;
            checkbox.checked = (rowData[1].permissions.includes(parseInt(permission.id))) ? true : false;
            td[permission.id].appendChild(checkbox);
        });
        td[td.length-1].setAttribute("data-id", rowData[0]);
        table.tBodies[0].appendChild(new_row);
    };

    this.updateOptions = () => {
        Object.values(document.update_permissions_form.entities.options).forEach(option => {
            if(Object.keys(this.selected_entities).indexOf(option.value) != -1){
                option.disabled = true;
            }else{
                option.disabled = false;
            }
        });        
    };

    this.addEvents = () => {
        //add button
        document.update_permissions_form.add_entity.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            var id = document.update_permissions_form.entities.value;
            this.selected_entities[id] = Object.entries(this.entities).find((entity) => {
                return entity[0] == id;
            });
            this.drawRow(this.selected_entities[id]);
            this.updateOptions();
        });
        //delete button
        document.table-permissions.addEventListener('click', (e) => {
            if(e.target.classList.contains('delete')){
                e.preventDefault();
                e.stopPropagation();
                var v = confirm("Delete?");
                if(v){
                    //delete from selected entities
                    delete this.selected_entities[e.target.parentNode.attributes["data-id"].value];
                    //delete row
                    var index = e.target.parentNode.parentNode.rowIndex;
                    document.getElementById("permissions-table").deleteRow(index);                    
                    //enable option
                    this.updateOptions();
                }
            };
        });
    };

    this.initialize = (url, permissions) =>{
        this.permissions = permissions;
        this.getData(url)
        .then(
            (data) => {
                this.entities = data;
                this.populate_entities();
                this.drawTable();
                this.addEvents();
                this.updateOptions();
                $("#entities").select2({theme : 'classic'});
            },
            (error) => {
                console.error(error);
                alert(error);
            });
    };
    
};

var initializePermissions = (url, permissions) => {
    var mp = new ManagePermissions();
    mp.initialize(url, permissions);
};



