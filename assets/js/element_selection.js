
function Element(data){
    return {
        id : data.id,
        displayName : data.displayName,
        name : data.name,
        selected : ko.observable(data.selected)
    }
};

function ElementSelection(eList, targetResult, form){
    
    this.getList = (eList) =>{
        var eList = (typeof(eList) == 'object') ? Object.values(eList) : eList;
        var _List = new Array();
        eList.forEach((e) => {
            _List.push(new Element(e));
        });
        return _List;
    };

    var self = this;
    this.filter = ko.observable('');
    this.targetResult = targetResult;    
    this.eList = ko.observableArray(this.getList(eList));

    this.eListFiltered = ko.computed(() => {
        if(this.filter() == ''){
            return self.eList();
        }else{
            return ko.utils.arrayFilter(self.eList(), (e) => {
                return e.displayName.includes(self.filter()) || e.name.includes(self.filter());
            });
        }
    });

    return {
        eListFiltered : this.eListFiltered,
        filter : this.filter,
        select_element : (element) => {
            element.selected((element.selected() == 1) ? 0 : 1);
        },
        submit: () => {
            var selected = ko.utils.arrayMap(
                ko.utils.arrayFilter((this.eList()), (e) => {
                    return e.selected();
                }), (e) => {
                return e.id;
            });
            target = document.getElementsByName(this.targetResult)[0];
            target.value = selected.toString();
            document.getElementById(form).submit();
        }
    };
};