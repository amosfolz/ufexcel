/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/users.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /ufexcel
 */

$(document).ready(function() {
    // Set up table of users
    $("#widget-ufexcel").ufTable({
        dataUrl: site.uri.public + "/api/ufexcel",
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind creation button
    bindCreationButton($("#widget-ufexcel"));

    // Bind table buttons
    $("#widget-ufexcel").on("pagerComplete.ufTable", function () {
        bindUfexcelButtons($(this));
    });
});
