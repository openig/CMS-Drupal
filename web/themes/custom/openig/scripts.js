(function($){
    $(document).ready(function(){
        $('#search_icon').click(function() {
            $('#search-block-form').toggleClass('open');
        });

        if($('.dropdown-item').hasClass('is-active')){
            var parent = $('.dropdown-item.is-active').closest('.nav-item.dropdown');
            var link = $(parent).find('.dropdown-toggle');
            $(link).addClass('show');
            var child = $(parent).find('.dropdown-menu');
            $(child).addClass('show');
        }
    })
})(jQuery);
