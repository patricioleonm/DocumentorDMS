$('#dashboard').masonry({
    itemSelector: '.grid-item', // use a separate class for itemSelector, other than .col-
    columnWidth: '.grid-sizer',
    percentPosition: true,
  });