(function( $ ){
    'use strict';
    $(function() {
        $('.current-episode-details').on('click','.btn-show-more-details',function(){
            if(!$('.details-content').hasClass('more')){
                $(this).text('SHOW LESS');
            }else{
                $(this).text('SHOW MORE');
            }
            $('.details-content').toggleClass('less more');
        });
    });
})( jQuery );
