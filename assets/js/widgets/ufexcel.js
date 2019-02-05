


function attachForm(table_id) {
$("body").on('renderSuccess.ufModal', function (data) {

var modal = $(this).ufModal('getModal');

console.log("var modal is:", modal);

var form = modal.find('.js-form');

console.log("var form is:", form);

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


//With this disabled the modal does not close and we are able to download the export. However, we need to modal to close/page to refresh.

/*
// Set up the form for submission
form.ufForm({
binaryCheckboxes: false
}).on("submitSuccess.ufForm", function() {

$(".modal").hide();
$(".modal-backdrop").hide();
console.log("var modal is:", modal);
//modal.ufModal("destroy")



});
*/

});
}

/*
$("#exporter").ufForm({
    msgTarget: $("#alerts-page")
}).on("submitSuccess.ufForm", function(event, data, textStatus, jqXHR) {
    redirectOnLogin(jqXHR);
});
*/






$(document).ready(function() {

//var test = $(this).closest('table').attr('id');


/*
var table_id = $("table:first").attr('id');
console.log("the table id:", table_id);
*/
/*
var find_table = $.inArray(table_id, site);
console.log("find_table inArray:", find_table);

console.log("site.ufexcel_tables = :", site["ufexcel_tables"]);
*/

/*
var menuOptions = site["ufexcel_tables"][table_id]["menu-options"];
console.log("the menu options:", menuOptions);
var table = site["ufexcel_tables"][table_id]["table"];
console.log("table is:", table);
*/


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
          }
          if (!(hidden.includes('template'))){
            $('li.ufexcel-template').show();
          }

/*
          //Sets menu options according to config
          $.each(menuOptions, function(key, value){
            console.log("value",value);
              if (value != "hidden"){
                    $('li.ufexcel-'+key).show();
  };
})*/



 }
});










/*
$.each(site["ufexcel_tables"], function(site, params){

 if (site == table_id){
//    console.log("The site is in the config:", site);

    var table = site["ufexcel_tables"][table_id]["table"];


    var contents = $('#table_id');
  //  console.log("the contents are:", contents);
  }
})
*/






$('.js-ufexcel-export').click(function() {

  var table_id = $("table:first").attr('id');

  $("body").ufModal({
  sourceUrl: site.uri.public + "/modals/ufexcel/export",
  ajaxParams: {
        table : table_id
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
          table : table_id
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
          table : table_id
         },
      msgTarget: $("#alerts-page")
          });

          attachForm(table_id);
          });


})
