(function($){
    $(document).ready(function(){

        // Afficher/masquer le menu en mobile
        $('.navigation_mobile .premiere_section i').click(function() {
            $('.region-mobile-seconde-section').toggleClass('open');
            $('.premiere_section').toggleClass('open');
            $('.section_background').toggleClass('open');
        });

        // Afficher/masquer barre de recherche
        $('#search_icon').click(function() {
            $('#views-exposed-form-resultats-de-recherche-block-1').toggleClass('open');
            $('#search_icon').toggleClass('open');
        });

        $('#views-exposed-form-resultats-de-recherche-block-1 .form-submit').val('');
        $('#block-openig-formulaireexposeresultats-de-rechercheblock-1-mobile .form-submit').val('');
        var media1200 = window.matchMedia("(min-width: 1200px)")
        if(media1200.matches){
            // Garder le sous-menu affiché si l'un de ses liens est actifs
            if($('.dropdown-item').hasClass('is-active')){
                var parent = $('.dropdown-item.is-active').closest('.nav-item.dropdown');
                var link = $(parent).find('.dropdown-toggle');
                $(link).addClass('show');
                var child = $(parent).find('.dropdown-menu');
                $(child).addClass('show');
            }
        }

        $('#block-openig-navigationprincipale .dropdown-toggle').click(function() {
            var parent = $('#block-openig-navigationprincipale .dropdown-toggle').closest('.nav-item.dropdown');
            $(parent).toggleClass('showChild');
        })
        $('#block-openig-navigationprincipale--2 .dropdown-toggle').click(function() {
            var parent = $('#block-openig-navigationprincipale--2 .dropdown-toggle').closest('.nav-item.dropdown');
            $(parent).toggleClass('showChild');
        })

        // Afficher/masquer icônes bouton partager
        $('#block-openig-addtoanysharebuttons #button_share').click(function () {
            $('#block-openig-addtoanysharebuttons .addtoany_list').toggleClass('open');
        })
        $('#block-openig-addtoanysharebuttons-bas-page #button_share_bas_page').click(function () {
            $('#block-openig-addtoanysharebuttons-bas-page .addtoany_list').toggleClass('open');
        })


        // Accordéons, ajout d'une class pour la bordure de gauche
        function bordureAccordeons() {
            $(".accordion .accordion-item").each(function(){
                var child = $(this).find('.accordion-button');
                if(!$(child).hasClass('collapsed')){
                    $(this).addClass('open');
                }else{
                    $(this).removeClass('open');
                }
            })
        }

        if(($('body').hasClass('page-node-type-article') || ($('body').hasClass('page-node-type-groupe-de-travail')) || ($('.paragraph').hasClass('paragraph--type--accordeon')))){
            bordureAccordeons();
            $('.accordion-button').click(function(){
                bordureAccordeons();
            })
        }

        // Affichage bloc Communiquer/Mutualiser
        $('#block-openig-communiquermutualiser .buttonTitle').click(function () {
            $('#block-openig-communiquermutualiser').toggleClass('contenthidden');
            $('#block-openig-communiquermutualiser .buttonTitle i').toggleClass('open')
        })


        // Ouverture de liens dans un nouvel onglet - dans le type de contenu Groupe de travail
        $(".node--type-groupe-de-travail #accordionGroupeTravail .accordion-item").each(function(){
                var link = $(this).find('.content_ressources a');
                $(link).attr('target','_blank');
        })

        $(".node--type-groupe-de-travail #block-openig-views-block-agenda-gt-agenda-gt .views-row").each(function(){
            var link = $(this).find('.views-field-title a');
            $(link).attr('target','_blank');
        })



        // Vue Agenda

        // Slide actif en fonction de la date du jour
        $(".slick--view--l-agenda .slick-slide").each(function(){
            var time = $(this).find('.views-field-field-dates-4 time');
            var datetime = $(time).attr('datetime');

            var dateNow = Date.now();
            var date = new Date(dateNow).toISOString();
            if(datetime >= date){
                var index = $(this).attr('data-slick-index');
                $('.slick__slider').slick('slickGoTo',index, true);
                return false;
            }
        })

        // Slide actif au clic sur un évènement de la page d'accueil
        if($('body').hasClass('path-l-agenda')){
            var url = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
            for (var i = 0; i < url.length; i++) {
                var urlparam = url[i].split('=');
                if(urlparam[0] == 'id'){
                    var id = urlparam[1];
                }
            }


            $(".slick--view--l-agenda .slick-slide").each(function(){
                var nid = $(this).find('.views-field-nid .field-content');
                if(id == $.trim(nid.text())){
                    var index = $(this).attr('data-slick-index');
                    $('.slick__slider').slick('slickGoTo',index, true);
                    return false;
                }
            })
        }



        // Fonction pour modifier l'année et afficher le contenu d'un évènement
        function change_evenement() {
            // Modification de l'année en haut du carousel
            var active = $('.slick--view--l-agenda .slick-current');
            var date = $(active).find(('.annee'));
            $('#annee').text($(date).text());

            // Affichage du contenu sous le carousel
            var contenu = $(active).find('.contenu');
            var clone = $(contenu).clone(true);
            $('#contenu_evenement').html(clone);
        }
        change_evenement();

        // Utilisation de la fonction change_evenement au clic sur les boutons précédent et suivant
        $('.slick--view--l-agenda .slick__arrow button').click(function() {
            change_evenement();
        })

        // Utilisation de la fonction change_evenement au glissement du carousel
        $('.slick--view--l-agenda .slick-list').mouseup(function() {
            change_evenement();
        })

        // Utilisation de la fonction change_evenement au clic sur un slide
        $('.slick--view--l-agenda .slick-slide').click(function() {
            var index = $(this).attr('data-slick-index');
            $('.slick__slider').slick('slickGoTo',index, true);
            change_evenement();
        })



        // Badge nombre de résultat - onglet contenu éditorial
        let onglet = $('#block-openig-local-tasks #nbContenuEditorial');
        let nbrElementNoFilter = onglet.data('id');

        let badge = $('#block-openig-local-tasks #nbContenuEditorial a');

        let headerSearch = $('.view-resultats-de-recherche #nbrContenu');
        let nbrElementFilter = headerSearch.data('id');

        var nbrElement = "";
        if(nbrElementFilter == undefined)
          nbrElement = nbrElementNoFilter;
        else
          nbrElement = nbrElementFilter;

        badge.append("<span class='badge rounded-pill bg-warning text-black'>"+nbrElement+"</span>");




        // Affichage du panneau de filtre
        $('.btn_filtre').on('click', function() {
            $('.formulaire_filtre').toggleClass('show');
        })
        $('.btn-close-filtre').on('click', function() {
            $('.formulaire_filtre').toggleClass('show');
        })

        // Modification de texte dans les filtres
        $('.views-exposed-form .form-item-exposed-from-date label').text('De');
        $('.views-exposed-form .form-item-exposed-to-date label').text('à');
        $('.views-exposed-form .form-item-exposed-from-date').before('<p class="labelDates">Dates</p>');


        // Ajout d'un margin top sur le fil d'ariane lorsque le sous-menu OPenIG est déplié
        if($('#block-openig-main-navigation .dropdown-menu').hasClass('show')){
            $('#block-openig-breadcrumbs .navigation_filAriane').addClass('menuOpen');
        }

        $('#block-openig-main-navigation .dropdown-toggle').on('click', function() {
            $('#block-openig-breadcrumbs .navigation_filAriane').toggleClass('menuOpen');
        })


        var media1200 = window.matchMedia("(max-width: 1200px)")

        if(media1200.matches){
            $('#block-openig-views-block-l-agenda-agenda-accueil .view-l-agenda .row').addClass('slick_agenda');
            if($('body').hasClass('path-frontpage')){
                $('.slick_agenda').slick({
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    infinite: true,
                    variableWidth: true,
                    swipeToSlide: true,
                    slidesPerRow: 1,
                    appendArrows: $('.slick__arrows'),
                    responsive: [
                        {
                          breakpoint: 700,
                          settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            infinite: true,
                            variableWidth: true,
                            swipeToSlide: true,
                            slidesPerRow: 1,
                            appendArrows: $('.slick__arrows'),
                          }
                        }
                    ]
                });
            }

            $('.view-les-services.view-display-id-services .view-grid-services .group_columns').addClass('slick_services');
            if($('body').hasClass('path-les-services')){
                $('.slick_services').slick({
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    infinite: true,
                    variableWidth: true,
                    swipeToSlide: true,
                    slidesPerRow: 1,
                    appendArrows: $('.slick__arrows'),
                    responsive: [
                        {
                        breakpoint: 700,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            infinite: true,
                            variableWidth: true,
                            swipeToSlide: true,
                            slidesPerRow: 1,
                            appendArrows: $('.slick__arrows'),
                        }
                        }
                    ]
                });
            }

            $('.view-les-groupes-de-travail.view-display-id-groupes_de_travail .view-grid-groupe .group_columns').addClass('slick_groupes');
            $('.view-les-groupes-de-travail.view-display-id-groupes_de_travail_archives .view-grid-groupe-archive .group_columns').addClass('slick_groupes_archives');

            if($('body').hasClass('path-les-groupes-de-travail')){
                $('.slick_groupes').slick({
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    infinite: true,
                    variableWidth: true,
                    swipeToSlide: true,
                    slidesPerRow: 1,
                    appendArrows: $('.view-les-groupes-de-travail.view-display-id-groupes_de_travail .slick__arrows'),
                    responsive: [
                        {
                        breakpoint: 700,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            infinite: true,
                            variableWidth: true,
                            swipeToSlide: true,
                            slidesPerRow: 1,
                            appendArrows: $('.view-les-groupes-de-travail.view-display-id-groupes_de_travail .slick__arrows'),
                        }
                        }
                    ]
                });

                $('.slick_groupes_archives').slick({
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    infinite: true,
                    variableWidth: true,
                    swipeToSlide: true,
                    slidesPerRow: 1,
                    appendArrows: $('.view-les-groupes-de-travail.view-display-id-groupes_de_travail_archives .slick__arrows'),
                    responsive: [
                        {
                        breakpoint: 700,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            infinite: true,
                            variableWidth: true,
                            swipeToSlide: true,
                            slidesPerRow: 1,
                            appendArrows: $('.view-les-groupes-de-travail.view-display-id-groupes_de_travail_archives .slick__arrows'),
                        }
                        }
                    ]
                });
            }
        }

        // Modal au clic sur le lien CKAN
        $("a.ckan").click(function(event){
            // Stop de l'event
            event.preventDefault();

            // Affichage de la modal
            $('#modalckan').modal('show');

            // Récupération du lien
            var link = $(this).attr("href");

            // Après un délai masquer la modal et ouverture du lien
            setTimeout(function() {
                $('#modalckan').modal('hide');
                window.open(link, '_blank');
            }, 2000);
        });

        if ($('body').hasClass('path-groupe')) {
            var title = $('#block-openig-page-title h1').text();
            $('.breadcrumb .breadcrumb-item:last').text(title);
        }

        if($('.view').hasClass('view-taxonomy-term')){
            $(".view-content .col-lg-3").each(function(){
                var type = $(this).find('.views-field-type .field-content');
                if($(type).text().replace(/\s+/g, '') == 'Evènement'){
                    var nid = $(this).find('.views-field-nid .field-content');
                    var title = $(this).find('.views-field-title .field-content a');
                    var more = $(this).find('.views-field-view-node .field-content a');
                    $(title).attr("href", "/l-agenda?id=" + $(nid).text().replace(/\s+/g, ''));
                    $(more).attr("href", "/l-agenda?id=" + $(nid).text().replace(/\s+/g, ''));
                }
                if($(type).text().replace(/\s+/g, '') == 'Ressource'){
                    var lien = $(this).find('.views-field-field-link .field-content');
                    var title = $(this).find('.views-field-title .field-content a');
                    var more = $(this).find('.views-field-view-node .field-content a');
                    var fichier = $(this).find('.views-field-field-file .field-content');
                    if(!$(lien).is(':empty')){
                        $(title).attr("href", $(lien).text().replace(/\s+/g, '')).attr('target', '_blank');
                        $(more).attr("href", $(lien).text().replace(/\s+/g, '')).attr('target', '_blank');
                    }else if(!$(fichier).is(':empty')){
                        $(title).attr("href", $(fichier).text().replace(/\s+/g, '')).attr('target', '_blank');
                        $(more).attr("href", $(fichier).text().replace(/\s+/g, '')).attr('target', '_blank');
                    }
                }
            })
        }
    })


  function process_facet (input_id, input_value_id) {
    // Bind category facet
    $(input_id + " input").on('change', function (e) {
      console.log('ici');
      var array = [];
      var checkboxes = $(input_id + " input:checked");

      // Get all checked checkboxes
      $.each(checkboxes, function() {
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

      // Auto submit form
      // $('#openig-search-bar').submit();
      // $('#edit-submit-resultats-de-recherche--2').submit();
    });
  }

  // Bind category facet
  process_facet('#edit-category-collapsible--2', '#facet_category_value');

  // Bind lineage facet
  process_facet('#edit-source-collapsible--2', '#facet_lineage_value');

  // Bind ckan_resource_format facet
  process_facet('#edit-format-collapsible--2', '#facet_resource_format_value');

})(jQuery);
