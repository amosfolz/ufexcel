


function attachForm() {
$("body").on('renderSuccess.ufModal', function (data) {
var modal = $(this).ufModal('getModal');
var form = modal.find('.js-form');

/**
* Set up modal widgets
*/
// Set up any widgets inside the modal
form.find(".js-select2").select2({
width: '100%'
});

// Set icon when changed
form.find('input[name=icon]').on('input change', function() {
$(this).prev(".icon-preview").find("i").removeClass().addClass($(this).val());
});

// Set up the form for submission
form.ufForm({
//validators: page.validators
}).on("submitSuccess.ufForm", function() {
// Reload page on success
window.location.reload();
});
});
}



$("#exporter").ufForm({
    msgTarget: $("#alerts-page")
}).on("submitSuccess.ufForm", function(event, data, textStatus, jqXHR) {
    redirectOnLogin(jqXHR);
});


$(document).ready(function() {

$('.js-ufexcel-export').click(function() {

  var table_name = $(this).data('table-name');

  $("body").ufModal({
  sourceUrl: site.uri.public + "/modals/ufexcel/export",
  ajaxParams: {
        table : table_name
     },
  msgTarget: $("#alerts-page")
      });

      attachForm();


    });

    $('.js-ufexcel-import-template').click(function() {

      var table_name = $(this).data('table-name');

      $("body").ufModal({
      sourceUrl: site.uri.public + "/modals/ufexcel/import/template",
      ajaxParams: {
            table : table_name
         },
      msgTarget: $("#alerts-page")
          });

          attachForm();
        });


    $('.js-ufexcel-import').click(function() {

      var table_name = $(this).data('table-name');

     $("body").ufModal({
      sourceUrl: site.uri.public + "/modals/ufexcel/import",
      ajaxParams: {
            table : table_name
         },
      msgTarget: $("#alerts-page")
          });

          attachForm();
          });


})
