var _aLookupWidgets = {};

function isUndefinedOrNull(value){
    if(value == null || value == undefined){ 
        return true; 
    }else{
        return false;
    }
};

function replaceChildNodes(element, child){
    Object.keys(element.options).forEach(x => element.options.remove(0));
    Object.values(child).forEach(x => element.options.add(x));
};

function getJSONLookupWidget(name) {
    if(!isUndefinedOrNull(_aLookupWidgets[name])) {
        return _aLookupWidgets[name];
    } else {
        return false;
    }
};

function JSONLookupWidget() {
    self = this;
};

JSONLookupWidget.prototype = {
    initialize : function(name, action) {
        this.sName = name;
        this.sAction = action;
        
        this.oSelectAvail = document.getElementById('select_' + name + '_avail');
        this.oSelectAssigned = document.getElementById('select_' + name + '_assigned');
        this.items_added = document.getElementById(name + '_items_added');
        this.items_removed = document.getElementById(name + '_items_removed');

        this.loading_layer = document.getElementById(name + '_loading');
        this.content_layer = document.getElementById(name + '_content');

        this.currentItems = Object.values(this.oSelectAssigned.options).map(x => x.value);

        document.getElementById(name + '_add').addEventListener('click', this.onclickAdd);
        document.getElementById(name + '_remove').addEventListener('click', this.onclickRemove);

        this.initialValuesLoaded = false;
        this.getValues();
    },

    getValues : function() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url : this.sAction + '&filter=%',
                type : 'GET',                
            })
            .done((data)=>{
                this.saveValues(JSON.parse(data));
                this.renderValues();
                this.loading_layer.classList.add('d-none');
                this.content_layer.classList.remove('d-none');
            })
            .fail((error) => {
                this.errGetValues(error);
            });
        });
    },

    errGetValues : function(res) {
        alert('There was an error retrieving data. Please check connectivity and try again.');
        this.oValues = {'off':'-- Error fetching values --'};
        this.renderValues();
    },

    saveValues : function(res) {
        this.oValues = res;
        return res;
    },

    renderValues : function() {
        var aOptions = [];
        var bSelFound = false;
        for(var k in this.oValues) {
            var found = false;
            for(var i=0; i<this.oSelectAssigned.options.length; i++) {
                if(this.oSelectAssigned.options[i].value == k) {
                    found = true; 
                    break;
                }
            }
    
            if(found) { 
                continue; 
            }    
                
            var aParam = { 'value' : k };
            if(k == 'off') {
                aParam['disabled'] = 'disabled';
            }
    
            var val = this.oValues[k];
            var sDisp = val;
            
            if(!isUndefinedOrNull(val['display'])) {
                var sDisp = val['display'];
                if(!isUndefinedOrNull(val['selected']) && val['selected'] === true) {
                    val['selected'] = undefined;
                    aParam['selected'] = true;
                    bSelFound = true;
                    aParam['value'] = k;
                }
            }
            var oO = new Option(sDisp,aParam['value'], aParam['selected']);
            aOptions.push(oO);
        }
        replaceChildNodes(this.oSelectAvail, aOptions);

        if(bSelFound) { 
            this.onclickAdd();
        }
    },

    updateValues : function() {
        var added = Object.values(this.oSelectAssigned.options).filter(x => !this.currentItems.includes(x.value)).map(x => x.value);
        var removed = this.currentItems.filter(x => !Object.values(this.oSelectAssigned.options).map(x => x.value).includes(x));
        this.items_added.value = added.toString();
        this.items_removed.value = removed.toString();
        console.log(added, removed);
    },
    
    onclickAdd : function(e) {
        e.preventDefault();
        e.stopPropagation();
        self.swapOptions(self.oSelectAvail, self.oSelectAssigned);
    },

    onclickRemove : function(e) {
        e.preventDefault();
        e.stopPropagation();
        self.swapOptions(self.oSelectAssigned, self.oSelectAvail);
    },

   swapOptions : (source, destination) => {
        var selected = Object.entries(source.options).map(x => x[1]).filter(x => x.selected);
        Object.entries(selected).forEach(x => destination.options.add(x[1]) );
        self.updateValues();
   }
};

function initJSONLookup(name, action) {
    _aLookupWidgets[name] = new JSONLookupWidget();
    _aLookupWidgets[name].initialize(name, action);
};


             
