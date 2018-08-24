function queryBuilderModel() {
    var criteriaGroups = ko.observableArray(criteria_groups);
    var selectedCriteria = ko.observableArray();
    var selectedMeta = ko.observableArray();

    return {
        criteriaGroups,
        selectedCriteria,
        selectedMeta,
    };
};
console.log(criteria_groups);
ko.applyBindings(new queryBuilderModel());