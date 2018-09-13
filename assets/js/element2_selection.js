
function Element(data){
    return {
        id : data.id,
        name : data.name,
        selected : ko.observable(data.selected),
        current : data.current
    }
};

function ElementSelection(eList, targetResultAdded, targetResultDeleted, form){
    
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
    this.targetResultAdded = targetResultAdded;
    this.targetResultDeleted  = targetResultDeleted;    
    this.eList = ko.observableArray(this.getList(eList));

    this.eListFiltered = ko.computed(() => {
        if(this.filter() == ''){
            return self.eList();
        }else{
            return ko.utils.arrayFilter(self.eList(), (e) => {
                return e.name.toUpperCase().includes(self.filter().toUpperCase());
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
            var deleted = ko.utils.arrayMap(
                ko.utils.arrayFilter((this.eList()),(e) => {
                    return e.selected() == 0 && e.current == 1;
                }),
                (e) => {
                    return e.id;
                }
            );
            targetSelected = document.getElementsByName(this.targetResultAdded)[0];
            targetDeleted = document.getElementsByName(this.targetResultDeleted)[0];
            targetSelected.value = selected.toString();
            targetDeleted.value = deleted.toString();
            document.getElementById(form).submit();
        }
    };
};