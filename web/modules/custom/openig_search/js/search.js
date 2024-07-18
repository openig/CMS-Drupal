(function ($, Drupal) {

  $(document).ready(function () {
    // Watch for inputs & fill search hidden fields
    function process_facet(input_id, input_value_id) {
      // Bind category facet
      $(input_id + " input").on('change', function (e) {
        var array = [];
        var checkboxes = $(input_id + " input:checked");

        // Get all checked checkboxes
        $.each(checkboxes, function () {
          if ($(this).prop('checked', true)) {
            array.push($(this).val());
          } else {
            array = array.filter(function (el) {
              return el !== $(this).val();
            })
          }
        });

        // Bind value
        $(input_value_id).val(array.join('|'));

      });
    }

      // Bind category facet
      process_facet('#edit-category-collapsible--2', '#facet_category_value');

      // Bind lineage facet
      process_facet('#edit-source-collapsible--2', '#facet_lineage_value');

      // Bind ckan_resource_format facet
      process_facet('#edit-format-collapsible--2', '#facet_resource_format_value');

      // Bind ckan_resource_format facet
      // process_facet('#facet_resource_data_type', '#facet_resource_data_type_value');


    function process_reset_facet(input_id, input_value_id) {
        var checkboxes = $(input_id + " input:checked");
        // Get all checked checkboxes
        $.each(checkboxes, function () {
          if ($(this).prop('checked', true)) {
            $(this).removeAttr('checked');
          }
        });
        // Reset value
        $(input_value_id).val('');
    }

    // Permet de reset les checkboxes
    $('#edit-reset').on('click', function (event){
      // Bind category facet
      process_reset_facet('#edit-category-collapsible--2', '#facet_category_value');

      // Bind lineage facet
      process_reset_facet('#edit-source-collapsible--2', '#facet_lineage_value');

      // Bind ckan_resource_format facet
      process_reset_facet('#edit-format-collapsible--2', '#facet_resource_format_value');

    });


    // Badge nombre de résultat ressources internes - onglet contenu éditorial
    // let onglet = $('#block-openig-tabs #nbContenuEditorial');
    let bloc = $('#block-openig-tabs');
    // let nbrElementOnglet = onglet.data('id');
    let nbrElementOnglet = bloc.data('id');

    let badge = $('#block-openig-tabs #nbContenuEditorial a');
    let headerSearch = $('.search-results .layout-content #nbrContenu');

    let nbrElementHeader = headerSearch.data('id');

    // onglet.attr('data-id', nbrElementHeader);
    bloc.attr('data-id', nbrElementHeader);

    var nbrElement = "";
    if (nbrElementHeader == undefined) {
      nbrElement = nbrElementOnglet;
    } else {
      nbrElement = nbrElementHeader;
    }
    badge.append("<span class='badge rounded-pill bg-warning text-black'>" + nbrElement + "</span>");

  });


})(jQuery, Drupal);

