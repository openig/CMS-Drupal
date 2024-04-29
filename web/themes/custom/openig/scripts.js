(function($){
    $(document).ready(function(){
        $('#search_icon').click(function() {
            $('#search-block-form').toggleClass('open');
            $('#search_icon').toggleClass('open');
        });

        if($('.dropdown-item').hasClass('is-active')){
            var parent = $('.dropdown-item.is-active').closest('.nav-item.dropdown');
            var link = $(parent).find('.dropdown-toggle');
            $(link).addClass('show');
            var child = $(parent).find('.dropdown-menu');
            $(child).addClass('show');
        }
        $('#block-openig-addtoanysharebuttons #button_share').click(function () {
            $('#block-openig-addtoanysharebuttons .addtoany_list').toggleClass('open');
        })
    })
})(jQuery);
