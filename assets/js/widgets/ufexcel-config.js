
/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        // Set up any widgets inside the modal
        form.find(".js-select2").select2({
            width: '100%'
        });

        // Set up the form for submission
        form.ufForm({
            validators: page.validators.ufexcel
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}








function bindUfexcelButtons(el){

    $('.js-edit').click(function() {

      var tableid =  $(this).data('tableid');

      $("body").ufModal({
        sourceUrl: site.uri.public + "/modals/ufexcel/edit",
        ajaxParams: {
            tableid : tableid
          },
      msgTarget: $("#alerts-page")
          });

            attachForm();
         });


    $('.js-edit-users').click(function() {

      var tableid =  $(this).data('tableid');

      $("body").ufModal({
      sourceUrl: site.uri.public + "/modals/ufexcel/edit/users",
      ajaxParams: {
          tableid : tableid
         },
      msgTarget: $("#alerts-page")
          });


          $("body").on('renderSuccess.ufModal', function (data) {
              var modal = $(this).ufModal('getModal');
              var form = modal.find('.js-form');

              // Set up collection widget
              var userWidget = modal.find('.js-form-ufexcel-users');
              userWidget.ufCollection({
                  dropdown: {
                      ajax: {
                          url     : site.uri.public + '/api/users'
                      },
                      placeholder : "Users"
                  },
                  dropdownTemplate: modal.find('#user-select-option').html(),
                  rowTemplate     : modal.find('#user-row').html()
              });

              // Get current pickuplists and add to widget
              $.getJSON(site.uri.public + '/api/ufexcel/' + tableid + '/users')
              .done(function (data) {
                  $.each(data['rows']['0']['users'], function (idx, user) {
                    console.log("var user is", user);
    
                      userWidget.ufCollection('addRow', user);
                  });
              });

              // Set up form for submission
              form.ufForm({
              }).on("submitSuccess.ufForm", function() {
                  // Reload page on success
                  window.location.reload();
              });
          });
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

          attachForm();
          });
}


function bindCreationButton(el) {
    // Link create button
    el.find('.js-ufexcel-create').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/ufexcel/create",
            msgTarget: $("#alerts-page")
        });

        attachForm();
    });
};
