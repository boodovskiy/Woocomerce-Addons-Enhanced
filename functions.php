<?php

/* Styles
=============================================================== */

function nm_child_theme_styles() {
	// Enqueue child theme styles
	wp_enqueue_style( 'nm-child-theme', get_stylesheet_directory_uri() . '/style.css' );
}

add_action( 'wp_enqueue_scripts', 'nm_child_theme_styles', 1000 ); // Note: Use priority "1000" to include the stylesheet after the parent theme stylesheets

function sc_include_myuploadscript() {
	/*
	 * I recommend to add additional conditions just to not to load the scipts on each page
	 * like:
	 * if ( !in_array('post-new.php','post.php') ) return;
	 */
	if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'myuploadscript', get_stylesheet_directory_uri() . '/js/upload.button.js', array( 'jquery' ), NULL, FALSE );
}

add_action( 'admin_enqueue_scripts', 'sc_include_myuploadscript' );

/*
* @param string $name Name of option or name of post custom field.
* @param string $value Optional Attachment ID
* @return string HTML of the Upload Button
*/
function sc_image_uploader_field( $name, $value = '' ) {
	$image      = ' button">Upload image';
	$image_size = 'thumbnail'; // it would be better to use thumbnail size here (150x150 or so)
	$display    = 'none'; // display state ot the "Remove image" button

	if ( $image_attributes = wp_get_attachment_image_src( $value, $image_size ) ) {

		// $image_attributes[0] - image URL
		// $image_attributes[1] - image width
		// $image_attributes[2] - image height

		$image   = '"><img src="' . $image_attributes[0] . '" style="max-width:95%;display:block;" />';
		$display = 'inline-block';
	}

	return '
		<div>
			<a href="#" class="sc_upload_image_button' . $image . '</a>
			<input type="hidden" name="' . $name . /*'" id="' . $name .*/
	       '" value="' . $value . '" />
			<a href="#" class="sc_remove_image_button" style="display:inline-block;display:' . $display . '">Remove image</a>
		</div>';
}

function addons_settings_fields( $post, $product_addons, $loop, $option ) {

	//echo "<td class='image_column'><input type='file' name='product_addon_option_image[" . $loop . "][]'/></td>";

	$meta_key   = 'product_addon_option_image[' . $loop . '][]';
	$upload_btn = sc_image_uploader_field( $meta_key, $option['image'] /*, get_post_meta($post->ID, $meta_key, true)*/ );

	echo "<td class='image_column'>" . $upload_btn . "</td>";

	d( $option );
}

add_action( 'woocommerce_product_addons_panel_option_row', 'addons_settings_fields', 10, 4 );

function addons_settings_fields_heading( $post, $product_addons, $loop ) {
	echo "<th class='image_column'>" . __( 'Option image', 'woocommerce-product-addons' ) . "</th>";
}

add_action( 'woocommerce_product_addons_panel_option_heading', 'addons_settings_fields_heading', 10, 3 );

function addons_add_image_field( $option ) {

	$option['image'] = "";

	return $option;
}

add_filter( 'woocommerce_product_addons_new_addon_option', 'addons_add_image_field' );

function addons_save_image_data( $data, $i ) {
	$option['image'] = ! empty( $_POST['product_addon_option_image'][ $i ] ) ? $_POST['product_addon_option_image'][ $i ] : "";

	for ( $ii = 0; $ii < sizeof( $option['image'] ); $ii ++ ) {
		$image = sanitize_text_field( stripslashes( $option['image'][ $ii ] ) );

		$data['options'][ $ii ]['image'] = $option['image'][ $ii ];
	}

	if ( sizeof( $data['options'] ) == 0 ) {
		continue; // Needs options
	}

	return $data;
}

add_filter( 'woocommerce_product_addons_save_data', 'addons_save_image_data', 10, 2 );

function show_addon_values( $addon ) {

	d( $addon );
}

add_action( 'wc_product_addon_end', 'show_addon_values', 10, 1 );

//$product_addons[] = apply_filters( 'woocommerce_product_addons_save_data', $data, $i );
