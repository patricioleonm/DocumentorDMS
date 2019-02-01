function activateRow(checkbox) {
    var row = breadcrumbFind(checkbox, 'TR');
    if (checkbox.checked) {
        addElementClass(row, 'activated');
    } else {
        removeElementClass(row, 'activated');
    }
}

function toggleSelectFor(source, nameprefix) {
    let state = source.checked;
    let checkboxes = document.querySelectorAll("input[type=checkbox][name^="+nameprefix+"]");
    checkboxes.forEach(x => x.checked = state);
}
