jQuery(function($){
  $('#rating-options').delegate('input','change',function(e){
    var ratingoptions = [];
    $('#rating-options').find('input').each(function(){
      var name = $(this).val();
      ratingoptions.push(name);
    });

    $('[name="reviews_rating_options"]').val(JSON.stringify(ratingoptions));
  });

  $('#rating-options').delegate('.delete-rating','click',function(e){
    e.preventDefault();
    $(this).closest('tr').remove();
    $('#rating-options input').first().trigger('change');
  });

  $('#rating-add-more').click(function(e){
    var ratingrow = $(' <tr><td class="left"><input type="text" class="regular-text"></td><td><a href="javascript:;" class="delete-rating">Delete</a></td></tr>');
    ratingrow.appendTo("#rating-options tbody");
    e.preventDefault();
  });


  $('#rating-options-casino').delegate('input','change',function(e){
    var ratingoptions = [];
    $('#rating-options-casino').find('input').each(function(){
      var name = $(this).val();
      ratingoptions.push(name);
    });

    $('[name="reviews_casino_rating_options"]').val(JSON.stringify(ratingoptions));
  });

  $('#rating-options-casino').delegate('.delete-rating','click',function(e){
    e.preventDefault();
    $(this).closest('tr').remove();
    $('#rating-options-casino input').first().trigger('change');
  });

  $('#rating-add-more-casino').click(function(e){
    var ratingrow = $(' <tr><td class="left"><input type="text" class="regular-text"></td><td><a href="javascript:;" class="delete-rating">Delete</a></td></tr>');
    ratingrow.appendTo("#rating-options-casino tbody");
    e.preventDefault();
  });

});
