<?php
/**
 * Enqueue admin scripts for our attached posts field
 */
function cmb2_attached_posts_field_scripts_styles() {

	$version = '1.1.0';
	$dir = trailingslashit( dirname( __FILE__ ) );

	if ( strtoupper( substr( PHP_OS, 0, 3 ) ) === 'WIN' ) {
		// Windows
		$content_dir = str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR );
		$content_url = str_replace( $content_dir, WP_CONTENT_URL, $dir );
		$url = str_replace( DIRECTORY_SEPARATOR, '/', $content_url );

	} else {
	  $url = str_replace(
			array( WP_CONTENT_DIR, WP_PLUGIN_DIR ),
			array( WP_CONTENT_URL, WP_PLUGIN_URL ),
			$dir
		);
	}

	$url = set_url_scheme( $url );

	wp_enqueue_script( 'jquery-ui', $url . 'js/lib/jquery-ui-1.10.4.custom.min.js', array( 'jquery' ), $version, true );
	wp_enqueue_script( 'cmb2-attached-posts-field', $url . 'js/attached-posts.js', array( 'jquery-ui' ), $version, true );
	wp_enqueue_style( 'cmb2-attached-posts-field', $url . 'css/attached-posts-admin.css', array(), $version );

}
add_action( 'admin_enqueue_scripts', 'cmb2_attached_posts_field_scripts_styles' );

/**
 * Add a CMB custom field to allow for the selection of multiple posts
 * attached to a single page
 */
function cmb2_attached_posts_fields_render( $field, $field_args, $value ) {

	// Setup our args
	$args = wp_parse_args( (array) $field->options( 'query_args' ), array(
		'post_type'			=> 'post',
		'posts_per_page'	=> 100,
		'orderby'			=> 'name',
		'order'				=> 'ASC',
	) );

	// Get our posts
	$posts = get_posts( $args );

	// If there are no posts found, just stop
	if ( ! $posts ) {
		return;
	}

	// Check to see if we have any meta values saved yet
	$attached = get_post_meta( $field->object_id, $field->_name(), true );

	// Set our count class
	$count = 0;

	// Wrap our lists
	echo '<div class="attached-posts-wrap widefat" data-fieldname="'. $field->_name() .'">';

	// Open our retrieved, or found posts, list
	echo '<div class="retrieved-wrap column-wrap">';
	echo '<h4 class="attached-posts-section">' . __( 'Available Posts', 'cmb' ) . '</h4>';
	echo '<ul class="retrieved connected">';

	// Loop through our posts as list items
	foreach ( $posts as $post ) {

		// Increase our count
		$count++;

		// Set our zebra stripes
		$zebra = $count % 2 == 0 ? 'even' : 'odd';

		// Set a class if our post is in our attached post meta
		$added = ! empty ( $attached ) && in_array( $post->ID, $attached ) ? ' added' : '';

		// Build our list item
		echo '<li data-id="', $post->ID ,'" class="' . $zebra . $added . '"><a title="'. __( 'Edit' ) .'" href="', get_edit_post_link( $post->ID ) ,'">', $post->post_title ,'</a><span class="dashicons dashicons-plus add-remove"></span></li>';

	}

	// Close our retrieved, or found, posts
	echo '</ul><!-- .retrieved -->';
	echo '</div><!-- .retrieved-wrap -->';

	// Open our attached posts list
	echo '<div class="attached-wrap column-wrap">';
	echo '<h4 class="attached-posts-section">' . __( 'Attached Posts', 'cmb' ) . '</h4>';
	echo '<ul class="attached connected">';

	// If we have any posts saved already, display them
	cmb2_attached_posts_fields_display_attached( $field, $attached );

	// Close up shop
	echo '</ul><!-- #attached -->';
	echo '</div><!-- .attached-wrap -->';
	echo '</div><!-- .attached-posts-wrap -->';

	// Display our description if one exists
	echo '<p class="cmb_metabox_description">', $field->desc(), '</p>';

}
add_action( 'cmb2_render_custom_attached_posts', 'cmb2_attached_posts_fields_render', 10, 3);

/**
 * Helper function to grab and filter our post meta
 */
function cmb2_attached_posts_fields_display_attached( $field, $attached ) {

	// Start with nothing
	$output = '';

	// If we do, then we need to display them as items in our attached list
	if ( ! $attached ) {

		echo '<input type="hidden" name="' . $field->id() . '">';
		return;

	}

	// Set our count to zero
	$count = 0;

	// Remove any empty values
	$attached = array_filter( $attached );

	// Loop through and build our existing display items
	foreach ( $attached as $post_id ) {

		// Increase our count
		$count++;

		// Set our zebra stripes
		$zebra = $count % 2 == 0 ? 'even' : 'odd';

		// Build our list item
		echo '<li data-id="' . $post_id . '" class="' . $zebra . '"><a title="'. __( 'Edit' ) .'" href="', get_edit_post_link( $post_id ) ,'">'.  get_the_title( $post_id ) .'</a><input type="hidden" value="' . $post_id . '" name="' . $field->id() . '[]"><span class="dashicons dashicons-minus add-remove"></span></li>';
	}

}