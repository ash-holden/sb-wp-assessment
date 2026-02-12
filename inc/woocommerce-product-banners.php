<?php
/**
 * Adds up to three banners to each PDP, using product-level meta content.
 * Creates meta box in Woocommerce CMS, saves to JSON and generates frontend output.
 * Each banner will only load if its image has been populated. Titles, text & links are optional.
 * Titles and text allow for HTML styling for maximum flexibility.
 * Setting a block to Background will create a full-width background image with the optional title, text & link centered.
 * Banners can also be set to Image on Left or Image on Right, for a two-column, 50/50 display. On mobile these will always stack with the image first.
 * Each banner has options to control text color and left vs center text alignment.
 * Banners section is inserted after the main two-column product section.
 */

/**
 * Add meta box to product edit screen
 */
function astra_child_banners_meta_box() {
    add_meta_box(
        'astra_child_product_banners',
        'Mixed Media Banners',
        'astra_child_banners_meta_box_callback',
        'product',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'astra_child_banners_meta_box' );

/**
 * Generate metabox fields (can adjust $max_banners add more banners)
 */
function astra_child_banners_meta_box_callback( $post ) {
    wp_nonce_field( 'astra_child_banners_save', 'astra_child_banners_nonce' );

    $banners = get_post_meta( $post->ID, '_product_banners', true );
    if ( ! is_array( $banners ) ) {
        $banners = array();
    }

    $max_banners = 3;
    ?>
    <style>
        .astra-banner-row { padding-top: 15px; padding-bottom: 15px; border-bottom: 1px solid #ccc;}
        .astra-banner-row:last-child { border-bottom: none;}
        .astra-banner-field { margin: 8px 0; }
        .astra-banner-field label { margin-right: 5px; }
        .astra-banner-field input { width: 300px; }
    </style>
    <p class="description">Add up to <?php echo (int) $max_banners; ?> banners. Each banner requires an image. Title, text & links are optional. Only banners with an image will display.</p>
    <div id="astra-banners-container">
        <?php
        for ( $i = 0; $i < $max_banners; $i++ ) {
            $b = isset( $banners[ $i ] ) ? $banners[ $i ] : array();
            $image_id = isset( $b['image_id'] ) ? (int) $b['image_id'] : 0;
            $layout   = isset( $b['layout'] ) ? sanitize_text_field( $b['layout'] ) : 'background';
            $title    = isset( $b['title'] ) ? $b['title'] : '';
            $text     = isset( $b['text'] ) ? $b['text'] : '';
            $color     = isset( $b['color'] ) ? sanitize_hex_color( $b['color'] ) : '#000000';
            $bg_color  = isset( $b['bg_color'] ) ? sanitize_hex_color( $b['bg_color'] ) : '';
            $text_align = isset( $b['text_align'] ) ? sanitize_text_field( $b['text_align'] ) : 'left';
            $link_text = isset( $b['link_text'] ) ? $b['link_text'] : '';
            $link_url  = isset( $b['link_url'] ) ? $b['link_url'] : '';

            $img_src = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
            ?>
            <div class="astra-banner-row" data-index="<?php echo (int) $i; ?>">
                <div class="astra-banner-row__header">
                    <strong>Banner <?php echo (int) $i + 1; ?></strong>
                </div>
                <div class="astra-banner-row__fields">
                    <div class="astra-banner-field">
                        <label>Image</label>
                        <div class="astra-banner-image-picker">
                            <input type="hidden" name="product_banners[<?php echo (int) $i; ?>][image_id]" value="<?php echo (int) $image_id; ?>" class="astra-banner-image-id" />
                            <div class="astra-banner-image-preview">
                                <?php if ( $img_src ) : ?>
                                    <img src="<?php echo esc_url( $img_src ); ?>" alt="" style="max-width: 100px; height: auto;" />
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button astra-banner-select-image">Select image</button>
                            <?php if ( $image_id ) : ?>
                                <button type="button" class="button astra-banner-remove-image">Remove</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="astra-banner-field">
                        <label>Layout</label>
                        <select name="product_banners[<?php echo (int) $i; ?>][layout]">
                            <option value="background" <?php selected( $layout, 'background' ); ?>>Background</option>
                            <option value="left" <?php selected( $layout, 'left' ); ?>>Image on Left</option>
                            <option value="right" <?php selected( $layout, 'right' ); ?>>Image on Right</option>
                        </select>
                    </div>
                    <div class="astra-banner-field">
                        <label>Title & text color</label>
                        <input type="text" name="product_banners[<?php echo (int) $i; ?>][color]" value="<?php echo esc_attr( $color ); ?>" class="astra-banner-color" placeholder="Optional - use hex code to customize" />
                    </div>
                    <div class="astra-banner-field">
                        <label>Background color</label>
                        <input type="text" name="product_banners[<?php echo (int) $i; ?>][bg_color]" value="<?php echo esc_attr( $bg_color ); ?>" class="astra-banner-color" placeholder="Optional - use hex code to customize" />
                    </div>
                    <div class="astra-banner-field">
                        <label>Text alignment</label>
                        <select name="product_banners[<?php echo (int) $i; ?>][text_align]">
                            <option value="left" <?php selected( $text_align, 'left' ); ?>>Left</option>
                            <option value="center" <?php selected( $text_align, 'center' ); ?>>Center</option>
                        </select>
                    </div>
                    <div class="astra-banner-field">
                        <label>Link text</label>
                        <input type="text" name="product_banners[<?php echo (int) $i; ?>][link_text]" value="<?php echo esc_attr( $link_text ); ?>" placeholder="Optional - leave blank to skip" />
                    </div>
                    <div class="astra-banner-field">
                        <label>Link URL</label>
                        <input type="text" name="product_banners[<?php echo (int) $i; ?>][link_url]" value="<?php echo esc_attr( $link_url ); ?>" placeholder="Enter URL" />
                    </div>
                    <div class="astra-banner-field">
                        <label>Title</label>
                        <input type="text" name="product_banners[<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $title ); ?>" placeholder="Optional - leave blank to skip." />
                    </div>
                    <div class="astra-banner-field">
                        <label>Text</label>
                        <textarea name="product_banners[<?php echo (int) $i; ?>][text]" rows="3" class="widefat" placeholder="Optional - leave blank to skip"><?php echo esc_textarea( $text ); ?></textarea>
                    </div>
                    
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
}

/**
 * Save meta box data
 */
function astra_child_banners_save( $post_id ) {
    if ( ! isset( $_POST['astra_child_banners_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['astra_child_banners_nonce'] ) ), 'astra_child_banners_save' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['product_banners'] ) || ! is_array( $_POST['product_banners'] ) ) {
        return;
    }

    $banners = array();
    $raw     = wp_unslash( $_POST['product_banners'] );

    foreach ( $raw as $row ) {
        $image_id = isset( $row['image_id'] ) ? (int) $row['image_id'] : 0;
        if ( ! $image_id ) {
            continue;
        }

        $layout    = isset( $row['layout'] ) ? sanitize_text_field( $row['layout'] ) : 'background';
        $layout    = in_array( $layout, array( 'background', 'left', 'right' ), true ) ? $layout : 'background';
        $title     = isset( $row['title'] ) ? wp_kses_post( $row['title'] ) : '';
        $text      = isset( $row['text'] ) ? wp_kses_post( $row['text'] ) : '';
        $color     = isset( $row['color'] ) ? sanitize_hex_color( $row['color'] ) : '#000000';
        $bg_color  = isset( $row['bg_color'] ) ? sanitize_hex_color( $row['bg_color'] ) : '';
        $text_align = isset( $row['text_align'] ) ? sanitize_text_field( $row['text_align'] ) : 'left';
        $text_align = in_array( $text_align, array( 'left', 'center' ), true ) ? $text_align : 'left';
        $link_text = isset( $row['link_text'] ) ? sanitize_text_field( $row['link_text'] ) : '';
        $link_url  = isset( $row['link_url'] ) ? esc_url_raw( $row['link_url'] ) : '';

        $banners[] = array(
            'image_id'    => $image_id,
            'layout'      => $layout,
            'title'       => $title,
            'text'        => $text,
            'color'       => $color ? $color : '#000000',
            'bg_color'    => $bg_color,
            'text_align'  => $text_align,
            'link_text'   => $link_text,
            'link_url'    => $link_url,
        );
    }

    update_post_meta( $post_id, '_product_banners', $banners );
}
add_action( 'save_post_product', 'astra_child_banners_save' );

/**
 * Admin scripts required for media picker
 */
function astra_child_banners_admin_scripts( $hook ) {
    global $post_type;

    if ( 'post.php' !== $hook || 'product' !== $post_type ) {
        return;
    }

    wp_enqueue_media();

    wp_add_inline_script( 'jquery', "
        jQuery(function($) {
            $(document).on('click', '.astra-banner-select-image', function(e) {
                e.preventDefault();
                var btn = $(this);
                var row = btn.closest('.astra-banner-row');
                var input = row.find('.astra-banner-image-id');
                var preview = row.find('.astra-banner-image-preview');

                var frame = wp.media({
                    library: { type: 'image' },
                    multiple: false
                });

                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    input.val(att.id);
                    preview.html('<img src=\"' + (att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url) + '\" alt=\"\" style=\"max-width:100px;height:auto;\" />');
                    if (!row.find('.astra-banner-remove-image').length) {
                        btn.after('<button type=\"button\" class=\"button astra-banner-remove-image\">Remove</button>');
                    }
                });

                frame.open();
            });

            $(document).on('click', '.astra-banner-remove-image', function(e) {
                e.preventDefault();
                var row = $(this).closest('.astra-banner-row');
                row.find('.astra-banner-image-id').val('');
                row.find('.astra-banner-image-preview').empty();
                $(this).remove();
            });
        });
    " );
}
add_action( 'admin_enqueue_scripts', 'astra_child_banners_admin_scripts' );

/**
 * Create banners on PDP
 */
function astra_child_output_product_banners() {
    if ( ! is_product() ) {
        return;
    }

    global $product;

    if ( ! $product instanceof WC_Product ) {
        return;
    }

    $banners = get_post_meta( $product->get_id(), '_product_banners', true );

    if ( ! is_array( $banners ) || empty( $banners ) ) {
        return;
    }

    ?>
    <div class="mixed-media-banners">
        <div class="mixed-media-banners__inner">
            <div class="mixed-media-banners__grid">
                <?php foreach ( $banners as $b ) : ?>
                    <?php
                    $image_id = isset( $b['image_id'] ) ? (int) $b['image_id'] : 0;
                    if ( ! $image_id ) {
                        continue;
                    }

                    $layout  = isset( $b['layout'] ) ? sanitize_text_field( $b['layout'] ) : 'background';
                    $title     = isset( $b['title'] ) ? $b['title'] : '';
                    $text      = isset( $b['text'] ) ? $b['text'] : '';
                    $color     = isset( $b['color'] ) ? sanitize_hex_color( $b['color'] ) : '#000000';
                    $bg_color  = isset( $b['bg_color'] ) ? sanitize_hex_color( $b['bg_color'] ) : '';
                    $text_align = isset( $b['text_align'] ) ? sanitize_text_field( $b['text_align'] ) : 'left';
                    $text_align = in_array( $text_align, array( 'left', 'center' ), true ) ? $text_align : 'left';
                    $link_text = isset( $b['link_text'] ) ? $b['link_text'] : '';
                    $link_url  = isset( $b['link_url'] ) ? esc_url( $b['link_url'] ) : '';

                    $content_style = '';
                    if ( $bg_color ) {
                        $content_style .= 'background-color:' . esc_attr( $bg_color ) . ';';
                    }
                    $content_style .= 'text-align:' . esc_attr( $text_align ) . ';';
                    $content_style_attr = $content_style ? ' style="' . $content_style . '"' : '';

                    $color_style = $color ? ' style="color:' . esc_attr( $color ) . ' !important;"' : '';

                    $item_class = 'mixed-media-banners__item mixed-media-banners__item--' . esc_attr( $layout ) . ' mixed-media-banners__item--align-' . esc_attr( $text_align );
                    ?>
                    <div class="<?php echo esc_attr( $item_class ); ?>">
                        <div class="mixed-media-banners__media">
                            <?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => '' ) ); ?>
                        </div>
                        <?php if ( $title || $text || ( $link_url && $link_text ) ) : ?>
                            <div class="mixed-media-banners__content"<?php echo $content_style_attr; ?>>
                                <?php if ( $title ) : ?>
                                    <h2 class="mixed-media-banners__title"<?php echo $color_style; ?>><?php echo wp_kses_post( $title ); ?></h2>
                                <?php endif; ?>
                                <?php if ( $text ) : ?>
                                    <div class="mixed-media-banners__text"<?php echo $color_style; ?>><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
                                <?php endif; ?>
                                <?php if ( $link_url && $link_text ) : ?>
                                    <a href="<?php echo esc_url( $link_url ); ?>" class="mixed-media-banners__link"<?php echo $color_style; ?>><?php echo esc_html( $link_text ); ?></a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'woocommerce_after_single_product_summary', 'astra_child_output_product_banners', 15 );