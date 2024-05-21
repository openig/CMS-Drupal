(function($){
    $(document).ready(function(){
        // Afficher/masquer barre de recherche
        $('#search_icon').click(function() {
            $('#search-block-form').toggleClass('open');
            $('#search_icon').toggleClass('open');
        });
        $('#block-openig-formulairederecherche .form-submit').val('');

        // Garder le sous-menu affiché si l'un de ses liens est actifs
        if($('.dropdown-item').hasClass('is-active')){
            var parent = $('.dropdown-item.is-active').closest('.nav-item.dropdown');
            var link = $(parent).find('.dropdown-toggle');
            $(link).addClass('show');
            var child = $(parent).find('.dropdown-menu');
            $(child).addClass('show');
        }

        // Afficher/masquer icônes bouton partager
        $('#block-openig-addtoanysharebuttons #button_share').click(function () {
            $('#block-openig-addtoanysharebuttons .addtoany_list').toggleClass('open');
        })
        $('#block-openig-addtoanysharebuttons-bas-page #button_share_bas_page').click(function () {
            $('#block-openig-addtoanysharebuttons-bas-page .addtoany_list').toggleClass('open');
        })

        // Accordéons fiche groupe de travail, ajout d'une class pour la bordure gauche
        function accordionGT() {
            $("#accordionGroupeTravail .accordion-item").each(function(){
                var child = $(this).find('.accordion-button');
                if(!$(child).hasClass('collapsed')){
                    $(this).addClass('open');
                }else{
                    $(this).removeClass('open');
                }
            })
        }
        if($('body').hasClass('page-node-type-groupe-de-travail')){
            accordionGT();
            $('.accordion-button').click(function(){
                accordionGT();
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
        $('.slick-list').mouseup(function() {
            change_evenement();
        })

        // Utilisation de la fonction change_evenement au clic sur un slide
        $('.slick--view--l-agenda .slick-slide').click(function() {
            $('.slick-slide.slick-active').removeClass('slick-active');
            $(this).addClass('slick-active');
            $('.slick-slide.slick-current').removeClass('slick-current');
            $(this).addClass('slick-current');
            change_evenement();
        })
    })
})(jQuery);
