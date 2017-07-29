/**
 * Created by Your Inspiration on 08/10/2015.
 */
jQuery(document).ready(function($){

    //collapse fields
    var collapse = $('.ywcwat_product_collapse'),
        image_file_frame;


    var watermark_url = null,
        watermark_id = null,
        this_preview   = null,
        table_position = null;

    collapse.each(function () {
        $(this).toggleClass('expand').nextUntil('div.ywcwat_product_collapse').slideToggle(100);
    });

    $(document).on('click','.ywcwat_product_collapse',function() {
        $(this).toggleClass('expand').nextUntil('div.ywcwat_product_collapse').slideToggle(100);
    });

    var get_row_index = function( element ){

            var id = element.parents('.ywcwat_product_watermark_row').attr('id');

            return id;
        },
        get_image_src = function( this_row ){

            var img_src = this_row.find('.ywcwat_product_wat_url').val();

            return img_src;
        },
        get_text_option = function( this_row ){

            var  text_watermark = this_row.find('.product_ywcwat_text'),
                font_size_wat = this_row.find('.product_ywcwat_font_size'),
                color_wat = this_row.find('.product_ywcwat_font_color'),
                bg_color_wat = this_row.find('.product_ywcwat_bg_color'),
                opacity_wat = this_row.find('.product_ywcwat_opacity'),
                width_text_box = this_row.find('.product_ywcwat_box_width'),
                height_text_box = this_row.find('.product_ywcwat_box_height'),
                line_height = this_row.find('.product_ywcwat_line_height'),
                font_name = this_row.find('.product_ywcwat_font'),
                angle = this_row.find('.ywcwat_text_angle')   ;

            var rgba_color = 'rgba('+hexToR( bg_color_wat.val() )+','+hexToG( bg_color_wat.val() )+','+hexToB( bg_color_wat.val() )+','+opacity_wat.val()/100+')';

            var data = {
                font_name : this_row.find('.product_ywcwat_font option:selected').text(),
                font_file_name : font_name.val(),
                font_size : font_size_wat.val(),
                color_wat : color_wat.val(),
                background_wat : rgba_color,
                width_box : width_text_box.val(),
                height_box : height_text_box.val(),
                text_wat : text_watermark.val(),
                line_height: line_height.val(),
                angle : angle.val()
            };

            return data;

        },
        set_size_preview = function( this_row ){

            var t = this_row.find('.ywcwat_preview_bg'),
                size_select = this_row.find('.ywcwat_product_size');

            var preview_width, preview_height;
            switch( size_select.val() ){
                case 'shop_single' :
                    preview_width = ywcwat_premium.shop_single.width == '' ? 'auto' : ywcwat_premium.shop_single.width+'px',
                        preview_height =  ywcwat_premium.shop_single.height == '' ? 'auto' : ywcwat_premium.shop_single.height+'px';
                    break;
                case 'shop_catalog' :
                    preview_width = ywcwat_premium.shop_catalog.width == '' ? 'auto' : ywcwat_premium.shop_catalog.width+'px',
                    preview_height =  ywcwat_premium.shop_single.height == '' ? 'auto' : ywcwat_premium.shop_catalog.height;
                    break;
                case 'shop_thumbnail':
                    preview_width = ywcwat_premium.shop_thumbnail.width == '' ? 'auto' : ywcwat_premium.shop_thumbnail.width+'px',
                    preview_height =  ywcwat_premium.shop_thumbnail.height == '' ? 'auto' : ywcwat_premium.shop_thumbnail.height+'px';
                    break;
                case 'full':

                    break;
            }

            t.css({
                'width' : preview_width,
                'height': preview_height
            });
        },
        preview_render = function( this_element ){


            var this_row = $('#'+get_row_index( this_element )),
                type_watermark =this_row.find('.ywcwat_select_type_wat').val(),
                preview_watermark = this_row.find('.ywcwat_preview_watermark'),
                settings_full_size = this_row.find('.ywcwat_settings_full_size'),
                selected_size = this_row.find('.ywcwat_product_size').val();

            if( selected_size=='full'){
                this_row.find('.product_ywcwat_preview').hide();
                settings_full_size.show();
            }else{
                this_row.find('.product_ywcwat_preview').show();
                settings_full_size.hide();
            }
            set_size_preview( this_row );

            var witdh_prev =  preview_watermark.parent().width(),
                height_prev = preview_watermark.parent().height();
            switch( type_watermark ){
                case 'type_img' :
                    var is_repeat = this_row.find('.ywcwat_repeat').is(':checked') ? 'repeat' :'no-repeat';
                    preview_watermark.removeAttr("style");
                    if( is_repeat == 'repeat' ) {
                        preview_watermark.html('');
                        preview_watermark.css({
                            'background': 'url(' + get_image_src(this_row) + ')',
                            'width': '100%',
                            'height': '100%',
                            'background-repeat': is_repeat
                        });
                    }else {
                        preview_watermark.html('<img src="' + get_image_src(this_row) + '" />');
                    }
                    break;
                case 'type_text':

                    preview_watermark.removeAttr("style");

                    var text_options = get_text_option( this_row),
                        unit = ywcwat_premium.gd_version >= 2 ? 'pt' : 'px',
                        width_box_size   =  witdh_prev * (text_options.width_box / 100),
                        height_box_size  =  height_prev *( text_options.height_box /100 ),
                        line_height_text;

                    if( text_options.line_height== -1 )
                        line_height_text = height_box_size;
                    else
                        line_height_text = text_options.line_height;

                    preview_watermark.html( '<span>'+text_options.text_wat+'</span>' );
                   // preview_watermark.html( text_options.text_wat );
                    preview_watermark.css({
                        'font-family' : text_options.font_file_name=='' ? 'inherit' : text_options.font_name ,
                        'font-size' : text_options.font_size+unit,
                        'color' : text_options.color_wat,
                        'background-color' : text_options.background_wat,
                        'width' : width_box_size+'px',
                        'height' : height_box_size+'px',
                        'line-height' : line_height_text+'px'
                    });

                    preview_watermark.find('span').css({
                        '-webkit-transform' :  'rotate(-'+text_options.angle+'deg )',
                        '-moz-transform': 'rotate(-'+text_options.angle+'deg )',
                        '-ms-transform' : 'rotate(-'+text_options.angle+'deg )',
                        '-o-transform' : 'rotate(-'+text_options.angle+'deg )',
                        'display' : 'inline-block'
                    });

                    break;
            }

            compute_position( this_element );

        },
        compute_position = function( this_element ){

            var this_row = $('#'+get_row_index( this_element ) ),
                position_select = this_row.find('.position_select'),
                label_position = this_row.find('.position_text'),
                preview_container = this_row.find('.ywcwat_preview_bg'),
                preview_watermark =preview_container.find('.ywcwat_preview_watermark:eq(0)'),
                width_preview = preview_watermark.outerWidth(true),
                height_preview = preview_watermark.outerHeight(true),
                hidden_field_pos = this_row.find('.product_ywcwat_pos_value:eq(0)'),
                margin_x = this_row.find('.ywcwat_prod_margin_x').val(),
                margin_y = this_row.find('.ywcwat_prod_margin_y').val(),
                p_top = 0,
                p_left= 0,
                p_right = 0,
                p_bottom= 0;

            if( position_select.hasClass( "ywcwat_top_left" ) ){

                p_top     = "calc( 0px + "+margin_y+"px )";
                p_left    = "calc( 0px + "+margin_x+"px )" ;
                p_right   = "auto";
                p_bottom  = "auto";
                hidden_field_pos.val('top_left');
                label_position.html( ywcwat_premium.label_position.top_left );

            }else if(position_select.hasClass( "ywcwat_top_center" ) ){

                p_top     = "calc( 0px + "+margin_y+"px )";
                p_left    =  'calc( 50% - '+width_preview/2+'px + '+margin_x+'px )';
                p_right   = "auto";
                p_bottom  = "auto";
                hidden_field_pos.val('top_center');
                label_position.html( ywcwat_premium.label_position.top_center );
            }else if(position_select.hasClass( "ywcwat_top_right" ) ){

                p_top     = "calc( 0px + "+margin_y+"px )";
                p_left    =  "auto";
                p_right   = "calc( 0px + "+margin_x+"px )" ;
                p_bottom  = "auto";
                hidden_field_pos.val('top_right');
                label_position.html( ywcwat_premium.label_position.top_right );

            }else if(position_select.hasClass( "ywcwat_middle_left" )){

                p_top = 'calc( 50% - '+height_preview/2+'px + '+margin_y+'px )';
                p_left = "calc( 0px + "+margin_x+"px )" ;
                p_right = "auto";
                p_bottom = "auto";
                hidden_field_pos.val('middle_left');
                label_position.html( ywcwat_premium.label_position.middle_left );

            }else if(position_select.hasClass( "ywcwat_middle_center") ){

                p_top = 'calc( 50% - '+height_preview/2+'px + '+margin_y+'px )';
                p_left    =  'calc( 50% - '+width_preview/2+'px + '+margin_x+'px )';
                p_right = "auto";
                p_bottom = "auto";
                hidden_field_pos.val('middle_center');
                label_position.html( ywcwat_premium.label_position.middle_center );

            }else if( position_select.hasClass( "ywcwat_middle_right")){

                p_top = 'calc( 50% - '+height_preview/2+'px + '+margin_y+'px )';
                p_left    =  'auto';
                p_right   = "calc( 0px + "+margin_x+"px )" ;
                p_bottom = "auto";
                hidden_field_pos.val('middle_right');
                label_position.html( ywcwat_premium.label_position.middle_right );


            }else if( position_select.hasClass( "ywcwat_bottom_left")){

                p_top= "auto";
                p_left    = "calc( 0px + "+margin_x+"px )" ;
                p_right = "auto";
                p_bottom = "calc( 0px + "+margin_y+"px )";
                hidden_field_pos.val('bottom_left');
                label_position.html( ywcwat_premium.label_position.bottom_left );

            }else if( position_select.hasClass( "ywcwat_bottom_center")){

                p_top= "auto";
                p_left    =  'calc( 50% - '+width_preview/2+'px )';
                p_right = "auto";
                p_bottom = "calc( 0px + "+margin_y+"px )";
                hidden_field_pos.val('bottom_center');
                label_position.html( ywcwat_premium.label_position.bottom_center );

            }else {
                //bottom right default
                p_top= "auto";
                p_left    =  'auto';
                p_right = "calc( 0px + "+margin_x+"px )" ;
                p_bottom = "calc( 0px + "+margin_y+"px )";
                hidden_field_pos.val('bottom_right');
                label_position.html( ywcwat_premium.label_position.bottom_right );
            }

            preview_watermark.css({'top':p_top,'bottom':p_bottom,'left':p_left, 'right':p_right});

            $('body').trigger('ywcwat_compute_position');
        },
        init_watermark_admin_field = function(){
            //show/hide current fields
            $('.ywcwat_select_type_wat').on('change',function(e){

                var t= $(this),
                    value_select = t.val(),
                    this_row = $('#'+get_row_index( t )),
                    text_field_container = this_row.find('.ywcwat_custom_wat_text_fields'),
                    img_field_container = this_row.find('.ywcwat_custom_wat_img_fields'),
                    general_field_container = this_row.find('.ywcwat_custom_general_fields'),
                    current_position = this_row.find('.position_select');


                switch (value_select){

                    case 'type_text' :
                        img_field_container.hide();
                        text_field_container.show();
                        general_field_container.show();

                        break;
                    case 'type_img' :
                        img_field_container.show();
                        text_field_container.hide();
                        general_field_container.show();
                        break;
                    case 'no':
                        img_field_container.hide();
                        text_field_container.hide();
                        general_field_container.hide();
                        break;

                }

                preview_render( current_position );


            }).change();
            $(document).on('click', '.ywcwat_product_image', function(e) {

                e.preventDefault();
                var t = $(this),
                    this_row = $('#'+get_row_index( t ) );
                watermark_url =this_row.find('.ywcwat_product_wat_url');
                watermark_id = this_row.find('input:hidden[id^="ywcwat_product_image_hidden-"]');
                this_preview   = this_row.find('.ywcwat_preview_watermark img');
                table_position = this_row.find('.ywcwat_bottom_right');

                // If the media frame already exists, reopen it.
                if (image_file_frame) {
                    image_file_frame.open();
                    return;
                }

                var downloadable_file_states = [
                    // Main states.
                    new wp.media.controller.Library({
                        library: wp.media.query({type: 'image'}),
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
                        type: 'image'
                    },
                    button: {
                        text: t.data('choose')
                    },
                    multiple: false,
                    states: downloadable_file_states
                });

                // When an image is selected, run a callback.
                image_file_frame.on('select', function() {


                    var  selection = image_file_frame.state().get('selection');

                    selection.map(function (attachment) {

                        attachment = attachment.toJSON();

                        if (attachment.url) {
                            watermark_id.val(attachment.id);
                            this_preview.attr('src', attachment.url );
                            watermark_url.val( attachment.url );

                            table_position.click();
                            preview_render(t);
                        }

                    });
                });

                // Finally, open the modal.
                image_file_frame.open();

            });
        },
        hexToR = function(h) {return parseInt((cutHex(h)).substring(0,2),16)},
        hexToG = function(h) {return parseInt((cutHex(h)).substring(2,4),16)},
        hexToB = function(h) {return parseInt((cutHex(h)).substring(4,6),16)},
        cutHex = function(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h};


    $(document).on('click', '.ywcwat_remove_product_watermark',function(e){
        e.preventDefault();
        var t = $(this),
            table_to_remove_id = t.data('element_id');

        var answer = window.confirm(ywcwat_premium.delete_single_watermark.confirm_delete_watermark);
        if( answer ) {
            $('#ywcwat_product_watermark_row-' + table_to_remove_id).remove();
            init_watermark_admin_field();
        }
        return false;
    });

    $(document).on('click','.product_ywcwat_container_position tr td', function(e){

        var t= $(this),
            table = t.parent().parent();

        table.find('td').removeClass( 'position_select' );
        t.addClass('position_select');
        preview_render(  t );

    });

    $(document).on('change', '.ywcwat_prod_margin_x, .ywcwat_prod_margin_y,.ywcwat_text_angle,.product_ywcwat_line_height,.product_ywcwat_font_size,.product_ywcwat_box_width,.product_ywcwat_box_height,.product_ywcwat_opacity, .product_ywcwat_font,.ywcwat_repeat', function(){

        var t = $(this);

        preview_render( t );
    });


    //when a text is typing
    $(document).on('change paste keyup input focus','.product_ywcwat_text',function(){

        var t= $(this);
        preview_render(t);

    });


    $(document).on('change','.ywcwat_product_size', function(){

        preview_render($(this));
    }).change();

    $(document).on('init_product_color_picker',function(){


        $('.product_colorpicker').wpColorPicker({
            change: function(event, ui){

                preview_render( $(this));
            }
        });

    }).trigger('init_product_color_picker') ;



    if( $('#_ywcwat_product_enabled_watermark').is(':checked') )
        $('.show_if_custom_watermark_enabled').show();
    else
        $('.show_if_custom_watermark_enabled').hide();

    $(document).on('change','#_ywcwat_product_enabled_watermark',function(){

        var t = $(this);

        if(t.is(':checked') )
            $('.show_if_custom_watermark_enabled').show();
        else
            $('.show_if_custom_watermark_enabled').hide();


    });

    $('.iris-palette').on('click', function(){
        setTimeout(preview_render($(this),1) );
    });

    $('body').on('initial_product_position_watermark', function(){
        init_watermark_admin_field();
    });

    init_watermark_admin_field();

});