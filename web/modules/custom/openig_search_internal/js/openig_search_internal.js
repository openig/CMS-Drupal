(function ($) {
  $(document).ready(function () {

    if ($('#block-openig-local-tasks')) {
      // Badge nombre de résultat - onglet contenu éditorial
      let onglet = $('#block-openig-local-tasks #nbContenuEditorial');
      let nbrElementNoFilter = onglet.data('id');

      let badge = $('#block-openig-local-tasks #nbContenuEditorial a');

      let headerSearch = $('.view-resultats-de-recherche #nbrContenu');
      let nbrElementFilter = headerSearch.data('id');

      var nbrElement = "";
      if (nbrElementFilter == undefined) {
        nbrElement = nbrElementNoFilter;
      } else {
        nbrElement = nbrElementFilter;
      }

      badge.append("<span class='badge rounded-pill bg-warning text-black'>" + nbrElement + "</span>");
    }
  });
})(jQuery);

