(function( $ ){
    'use strict';
    $(function() {
        var navElems = $('.admin-meta-box-tabbable');
        navElems.each(function(i,thisElem){
            var navTabs = $(thisElem).children('.nav-tab-wrapper'),
            tabIndex = null;
            navTabs.children('.nav-tab').each(function(i,thisTab) {
                $(thisElem)
                .children( 'div.tab-content:not(:first-of-type)').addClass('hidden');
                $( thisTab ).on( 'click', function( evt ) {
                    evt.preventDefault();

                    if ( ! $( thisTab ).hasClass( 'nav-tab-active' ) ) {

                        $( '.nav-tab-active' ).removeClass( 'nav-tab-active' );
                        $( thisTab ).addClass( 'nav-tab-active' );

                        tabIndex = $( thisTab ).index();

                        $(thisElem)
                        .children( 'div.tab-content:not( .inside.hidden )' )
                        .addClass( 'hidden' );

                        $(thisElem)
                        .children( 'div.tab-content:nth-child(' + ( tabIndex ) + ')' )
                        .addClass( 'hidden' );

                        $(thisElem)
                        .children( 'div.tab-content:nth-child( ' + ( tabIndex + 2 ) + ')' )
                        .removeClass( 'hidden' );

                    }
                });
            });
        });
        // Screenshots scripts
        var frame,
        metaBox = $('#gr_screenshots.postbox'), // Your meta box id here
        addImgLink = metaBox.find('.upload-custom-img'),
        imgContainer = metaBox.find( '.screenshots-img-container'),
        imgIdInput = metaBox.find( '[name="_gr_screenshots"]' );

        addImgLink.on( 'click', function( event ){
            event.preventDefault();
            if ( frame ) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: 'Select or Upload Screenshots',
                button: {
                    text: 'Add to screenshots'
                },
                multiple: 'add'
            });
            frame.on( 'select', function() {
                var attachment = frame.state().get('selection').toJSON();
                var attachmentids = [];
                imgContainer.empty();
                $.each(attachment,function(i,v){
                    if(typeof v.url != 'undefined'){
                        imgContainer.append( '<img src="'+v.url+'" alt="" style="max-height:100px;"/>' );
                        attachmentids.push(v.id);
                    }
                })
                imgIdInput.val( attachmentids.toString() );
            });
            frame.on('open',function() {
                var selection = frame.state().get('selection');
                var ids = imgIdInput.val().split(',');
                ids.forEach(function(id) {
                    var attachment = wp.media.attachment(id);
                    attachment.fetch();
                    selection.add( attachment ? [ attachment ] : [] );
                });
            });
            frame.open();
        });

        // Video scripts
        var frame_vid,
        metaBox_vid = $('[id$="_video_review"]').first(), // Your meta box id here
        addImgLink_vid = metaBox_vid.find('.upload-custom-img'),
        imgContainer_vid = metaBox_vid.find( '.video-review-container'),
        imgIdInput_vid = metaBox_vid.find( '[name$="_video"]' );

        if(imgIdInput_vid.length && imgIdInput_vid.val().length > 0){
            var vid_data = $.parseJSON(imgIdInput_vid.val());

            if(vid_data.type=='link'){
                $.ajax({
                    url: "/wp-admin/admin-ajax.php",
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        'action':'parse-embed',
                        'post_ID': $('#post_ID').val(),
                        'shortcode': '[embed]'+vid_data.embed.url+'[/embed]'
                    },
                    success: function(results){
                        metaBox_vid.find('.embed-code-wrapper').html(results.data.body);
                    },
                    error: function(errorThrown){console.log(errorThrown);}
                });
            }
        }

        addImgLink_vid.on( 'click', function( event ){
            event.preventDefault();
            if ( frame ) {
                frame_vid.open();
                return;
            }
            frame_vid = wp.media({
                title: 'Select or Upload Video',
                button: {
                    text: 'Select Video'
                },
                multiple: false,
                frame:    "post",
                state:    "embed",
            });
            frame_vid.on( 'select', function() {

                var state = frame_vid.state(),
                type = state.get('type'),
                embed = state.props.toJSON();

                embed.url = embed.url || '';

                imgIdInput_vid.val(JSON.stringify({
                    type : type,
                    embed : embed
                }));
                if(type=='link'){
                    $.ajax({
                        url: "/wp-admin/admin-ajax.php",
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'action':'parse-embed',
                            'post_ID': $('#post_ID').val(),
                            'shortcode': '[embed]'+embed.url+'[/embed]'
                        },
                        success: function(results){
                            metaBox_vid.find('.embed-code-wrapper').html(results.data.body);
                        },
                        error: function(errorThrown){console.log(errorThrown);}
                    });
                }
            });
            frame_vid.on('open',function() {
                var selection = frame_vid.state().get('selection');
            });
            frame_vid.open();
        });

        $('#rating-options tbody').delegate('input','change',function(e){
            var ratingvalue = {};
            ratingvalue.length = 0;
            var totalscore = 0;
            $('#rating-options tbody').find('label').each(function(){
                var name = $(this).text();
                var score = parseInt($(this).closest('tr').find('input').first().val()) || 0;
                if(score>0){
                    ratingvalue[name] = score;
                    ratingvalue.length++;
                    totalscore = totalscore + score;
                }
            });
            $('#rating-options span.total-rating').text((totalscore/(ratingvalue.length)).toFixed(2));
            $('[name$="_score"]').val(JSON.stringify(ratingvalue));
        });

        $('[name="input_gr_casino"]').suggest("/wp-admin/admin-ajax.php?action=game_casino_search", {multiple:true, multipleSep: ","})
        $('#casino_tag .casinoadd').click(function(){
            var current_tags = $('[name="_gr_casino"]').val().split(',');
            var new_tags = $('[name="input_gr_casino"]').val().trim().replace(/,/i, "").split(',');
            Array.prototype.push.apply(current_tags,new_tags);
            var newvalue = [];
            var checklistdiv = $(this).closest('.tagsdiv').find('.tagchecklist');
            checklistdiv.empty();
            for(var i in current_tags){
                if(newvalue.indexOf(current_tags[i]) === -1){
                    newvalue.push(current_tags[i]);
                    checklistdiv.append('<span><a id="casino_tag-check-num-'+i+'" class="ntdelbutton" tabindex="0">X</a>&nbsp;'+current_tags[i]+'</span>');
                }
            }
            $('[name="_gr_casino"]').val(newvalue.join(','));
            $('[name="input_gr_casino"]').val('');
        });

        $('#casino_tag').delegate('.tagchecklist .ntdelbutton','click',function(){
            var current_tags = $('[name="_gr_casino"]').val().split(',');
            var thistagtext = $(this).closest('span').clone();
            thistagtext.find('a').remove();
            thistagtext = thistagtext.text().trim();
            var found = current_tags.indexOf(thistagtext);
            if (found > -1) {
                current_tags.splice(found, 1);
            }
            $('[name="_gr_casino"]').val(current_tags.join(','));
            $(this).closest('span').remove();
        });
    });
})( jQuery );
