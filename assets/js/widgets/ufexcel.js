

$(document).ready(function() {

  //Check each table on the page against the configuration in site.ufexcel_tables.
    $('table').each(function() {
        var table_id =  $(this).attr('id');

        //Get the parent div
        var div = $(this).parents().closest('div[id]');

        // menu options for that table
        var options = div.children().find("[class^=ufexcel]");


            $.ajax({
    type: 'GET',
    url : site.uri.public + '/api/ufexcel/features/' + table_id,
    success: function(data){
      response = data;


      if (response != null && response.features.import === true)
         {
            $('li.ufexcel-import').show();
            $('li.ufexcel-template').show();
          }

      if (response != null && response.features.export === true)
          {
             $('li.ufexcel-export').show();
          }
        }
      });
    });
  });








function attachImportForm(table_id) {
$("body").on('renderSuccess.ufModal', function (data) {

var modal = $(this).ufModal('getModal');
var form = modal.find('.js-form');


//Set the "table" form field value to table_id
$("input[name=table]").val(table_id);

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
binaryCheckboxes: false
}).on("submitSuccess.ufForm", function() {

window.location.reload();

});

});
}

function attachForm(table_id) {
$("body").on('renderSuccess.ufModal', function (data) {

var modal = $(this).ufModal('getModal');
var form = modal.find('.js-form');


//Set the "table" form field value to table_id
$("input[name=table]").val(table_id);

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

});
}






$(document).ready(function() {


  $(window).bind ("beforeunload",  function () {
      /* This code will fire just before the Individual-file Download
         dialog opens.
         Close the after the server sends back file.
      */

      $('.modal').modal('hide');
  } );

/*
//Check each table on the page against the configuration in site.ufexcel_tables.
var all_tables = $('table').each(function() {

  var table_id =  $(this).attr('id');
  var config = site["ufexcel"];

//Get the parent div
var div = $(this).parents().closest('div[id]');

// menu options for that table
var options = div.children().find("[class^=ufexcel]");


 if (table_id in config && site["ufexcel"][table_id]["hidden"] != undefined)
        {
          var hidden = site["ufexcel"][table_id]["hidden"];

          if (!(hidden.includes('export'))){
            $('li.ufexcel-export').show();
          }
          if (!(hidden.includes('import'))){
            $('li.ufexcel-import').show();
            $('li.ufexcel-template').show();
          }
 }
});

*/



$('.js-ufexcel-export').click(function() {

  var table_id = $("table:first").attr('id');

  $("body").ufModal({
  sourceUrl: site.uri.public + "/modals/ufexcel/export",
  ajaxParams: {
        tableid : table_id
     },
  msgTarget: $("#alerts-page")
      });

      attachForm(table_id);
    });

    $('.js-ufexcel-import-template').click(function() {

      var table_id = $("table:first").attr('id');

      $("body").ufModal({
      sourceUrl: site.uri.public + "/modals/ufexcel/import/template",
      ajaxParams: {
          tableid : table_id
         },
      msgTarget: $("#alerts-page")
          });

          attachForm(table_id);
        });


    $('.js-ufexcel-import').click(function() {

        var table_id = $("table:first").attr('id');

     $("body").ufModal({
      sourceUrl: site.uri.public + "/modals/ufexcel/import",
      ajaxParams: {
          tableid : table_id
         },
      msgTarget: $("#alerts-page")
          });

          attachImportForm(table_id);
          });


})
