/**
 * Created by Your Inspiration on 16/09/2015.
 */

jQuery(document).ready(function($){

    var image_file_frame;

/*open media libray for add image*/
    $(document).on('click', '#ywcwat_add_watermark', function(e) {


        e.preventDefault();
        var t = $(this),
            hidden_field = $('#ywcwat_watermark_select'),
            img = $('.ywcwat_preview img');

        // If the media frame already exists, reopen it.
        if (image_file_frame) {
            image_file_frame.open();
            return;
        }

        var downloadable_file_states = [
            // Main states.
            new wp.media.controller.Library({
                library: wp.media.query({type: 'image/png'}),
                multiple: false,
                title: t.data('choose'),
                priority: 20,
                filterable: 'all'
            })
        ];

        // Create the media frame.
        image_file_frame = wp.media.frames.downloadable_file = wp.media({
            // Set the title of the modal.
            title: t.data('choose'),
            library: {
                type: 'image/png'
            },
            button: {
                text: t.data('choose')
            },
            multiple: false,
            states: downloadable_file_states
        });

        // When an image is selected, run a callback.
        image_file_frame.on('select', function () {

            var file_path = '',
                file_id = '',
                selection = image_file_frame.state().get('selection');

            selection.map(function (attachment) {

                attachment = attachment.toJSON();

                if (attachment.url) {

                    var attach_width = attachment.width,
                        attach_height = attachment.height,
                        limit_w = ywcwat_params.perc_w* 1,
                        limit_h = ywcwat_params.perc_h*1;

                    if( ( limit_h > 0 && attach_height > limit_h )|| ( limit_w > 0 &&  attach_width> limit_w ) ){

                        $('.ywcwat_messages').removeClass('complete_task complete_all_task').addClass('error_size');
                        $('.ywcwat_icon').removeClass('dashicons-yes').addClass('dashicons dashicons-no');

                        var text = ywcwat_params.messages.error_watermark_sizes;

                        $('.ywcwat_text').html( text );
                        $('.ywcwat_messages').show();


                        return false;
                    }
                    $('.ywcwat_messages').hide();

                    file_path = attachment.url;
                    file_id = attachment.id;

                    hidden_field.val(attachment.id);
                    img.attr('src', attachment.url );
                    $('.ywcwat_url').val( attachment.url );
                }

            });
        });

        // Finally, open the modal.
        image_file_frame.open();
    });


    var current_index = 0;

    $(document).on('click', '.ywcwat_apply_all_watermark', function(e){
        e.preventDefault();

        var t = $(this),
            attach_ids = JSON.parse( ywcwat_params.attach_id),
            total_img = attach_ids.length,
            progressbar = $('#ywcwat-progressbar_all'),
            progressbar_perc = $('#ywcwat-progressbar-percent_all'),
            message_container = $('.ywcwat_messages');


        message_container.hide();
        //initialize progressbar
        progressbar.progressbar();
        progressbar_perc.html("0%");

        progressbar.show();
        var max_item_for_run = ywcwat_params.max_item_action == '' ? total_img : ywcwat_params.max_item_action* 1;

        if( current_index == total_img)
            current_index=0;


        var i = 0;

        t.prop('disabled',true);


        function ApplyAllWatermark( attach_id ){

            var data = {
                ywcwat_attach_id : attach_id,
                action : ywcwat_params.actions.apply_all_watermark
            };

            $.ajax({

                type: 'POST',
                url: ywcwat_params.ajax_url,
                data: data,
                dataType: 'json',
                success: function(response) {
                    //finish
                    if( current_index==total_img && typeof response.result=='undefined' ){

                        message_container.removeClass('complete_task').addClass('complete_all_task');
                        message_container.find('.ywcwat_icon').addClass('dashicons dashicons-yes');
                        message_container.find('.ywcwat_text').html(ywcwat_params.messages.complete_all_task);
                        message_container.show();
                        t.prop('disabled', false);
                        progressbar.hide();
                    }

                    if( response.result== 'create_watermark' || response.result=='error_get_img_editor' || response.result== 'error_save_img_resized' || response.result=='empty_path' || response.result=='skip' ){

                        progressbar.progressbar( "value", ( (current_index+1) / total_img ) * 100 );
                        progressbar_perc.html( Math.round( ( (current_index+1)/ total_img ) * 1000 ) / 10 + "%" );

                        if( i<max_item_for_run && current_index<total_img ){
                            i++;
                            current_index++;
                            ApplyAllWatermark( attach_ids[current_index]);
                        }
                        else{
                            t.prop('disabled', false);
                            progressbar.hide();
                            message_container.addClass('complete_task');
                            message_container.find('.ywcwat_icon').addClass('dashicons dashicons-yes');

                            var text = ywcwat_params.messages.complete_single_task+' '+current_index+' '+ywcwat_params.messages.more_product+' '+ywcwat_params.messages.on+' '+total_img;

                            message_container.find('.ywcwat_text').html( text );
                            message_container.show();

                        }


                    }
                }
            });

        }


        ApplyAllWatermark( attach_ids[current_index] );

    });

    $(document).on('click', '#ywcwat_reset_watermark', function(e){

        var t= $(this);

        var answer = window.confirm(ywcwat_params.messages.reset_confirm);

        if( answer ) {

            var data = {
                ywcwat_remove_watermark: 'remove',
                action: ywcwat_params.actions.remove_watermark
            };
            $.ajax({

                type: 'POST',
                url: ywcwat_params.ajax_url,
                data: data,
                dataType: 'json',
                success: function (response) {

                    $('.ywcwat_messages').addClass('complete_task');
                    $('.ywcwat_icon').addClass('dashicons dashicons-yes');

                    var text;

                    if( response.success==0 || response.success > 1 ){

                        text = response.success+" "+ywcwat_params.messages.plural_success_image+", ";

                    }else
                    {
                        text = response.success+" "+ywcwat_params.messages.singular_success_image+", ";
                    }

                    if( response.error==0 || response.error > 1 ){

                        text+=response.error+" "+ywcwat_params.messages.plural_error_image+", ";

                    }else{

                        text+=response.error+" "+ywcwat_params.messages.singular_error_image+", ";
                    }

                    $('.ywcwat_text').html( text );
                    $('.ywcwat_messages').show();

                }
            });
        }
    });

   });