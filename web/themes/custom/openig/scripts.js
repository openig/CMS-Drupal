(function($){
    $(document).ready(function(){
        // Afficher/masquer barre de recherche
        $('#search_icon').click(function() {
            $('#search-block-form').toggleClass('open');
            $('#search_icon').toggleClass('open');
        });

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
    })
})(jQuery);
