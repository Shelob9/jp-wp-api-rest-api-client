<?php
/**
 * The client
 *
 * @package   jp-rest-api-client
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */
/**
 * Makes a GET request to the WP REST API & returns JSON Object
 *
 * @param string $url URL to GET
 *
 * @return array|WP_Error Array of post objects on success or WP_Error object on failure.
 */
function jp_rest_client_get_json( $url ) {
	//GET the remote site
	$response = wp_remote_get( $url );

	//Check for error
	if ( is_wp_error( $response ) ) {
		return sprintf( 'The URL %1s could not be retrieved.', $url );
	}

	//get just the body
	$data = wp_remote_retrieve_body( $response );

	//return if not an error
	if ( ! is_wp_error( $data )  ) {

		//decode and return
		return json_decode( $data );

	}

}

/**
 * Builds a URL string for making GET requests for the WP REST API
 *
 * @see http://wp-api.org/#posts_retrieve-posts
 *
 * @param string|array $post_types Post type(s) to query for. Can be on post type as a string or an array of post types. Default is 'post'
 * @param bool|array   $filters Optional. Filters to use in query. Should be an array in form of filter => value. See REST API docs for possible values. The default is false, which skips adding filters.
 * @param string $base Optional. Base for making request. Default is 'wp-json/posts?
 *
 * @return string|void
 */
function jp_rest_client_posts_url_string( $post_types = 'post', $filters = false, $rooturl = 'home_url', $base = '/wp-json/posts?' ) {
	if ( is_callable( $rooturl ) ) {
		$url = call_user_func( $rooturl );
		$url = $url.$base;
	}
	else {
		$url = $rooturl.$base;
	}

	if ( is_string(  $post_types ) ) {
		$post_types = array( $post_types);
	}
	foreach ( $post_types as $type ) {
		$url = add_query_arg( "type[]", $type, $url  );
	}

	if ( $filters ) {

		foreach( $filters as $filter => $value ) {
			$args[ "filter[{$filter}]" ] =  $value;
		}
		$url = add_query_arg( $args, $url );
	}

	return $url;

}

/**
 * Insert a post form a remote site on current site
 *
 * @param $post A post returned by the REST API. Result of jp_rest_client_get_json() will work.
 *
 * @return int|string|WP_Error
 */
function jp_rest_client_insert_post_from_json( $post ) {

	//check we have an array or object
	//either make sure its an array or throw an error
	if ( is_array( $post ) || is_object( $post )  ) {
		//ensure $post is an array, converting from object if need be
		$post = (array) $post;
	}
	else {
		return sprintf( 'The data inputted to %1s must be an object or an array', __FUNCTION__ );
	}


	//set up an array to do most of the conversion in one loop
	//Note: We set ID as import_id to ATTEMPT to use the same ID
	//Leaving as ID would UPDATE an existing post of the same ID
	$convert_keys = array(
		'title' => 'post_title',
		'content' => 'post_content',
		'slug' => 'post_name',
		'status' => 'post_status',
		'parent' => 'post_parent',
		'excerpt' => 'post_excerpt',
		'date' => 'post_date',
		'type' => 'post_type',
		'ID' => 'import_id',
	);

	//copy FROM json array TO how wp_insert_post() wants it and unset old key
	foreach ( $convert_keys as $from => $to ) {
		if ( isset( $post[ $from ] ) ) {
			$post[ $to ] = $post[ $from ];
			unset( $post[ $from ] );
		}

	}

	//prepare author ID
	$post[ 'post_author' ] = $post[ 'author' ]->ID;
	unset( $post[ 'author' ] );

	//put terms object into it's own array and unset
	$terms = (array) $post[ 'terms' ];

	unset( $post[ 'terms'] );

	//create post and return its ID
	$id =  wp_insert_post( $post );
	/**
	 * Exposes the component of the JSON array that has the taxonomy information it it via an action to be handled as needed.
	 *
	 * @param array $terms The terms
	 * @param int $id The ID of the post that was created.
	 * @param int $original_id ID of the post being copied from
	 */
	add_action( 'jp_rest_client_terms_on_import', $terms, $id, $post[ 'import_id' ]  );

	return $id;


}

/**
 * Create a post on a remote site.
 *
 * @param object $post A post from any site, as returned by REST API
 * @param string $auth Authentication to add to POST request headers.
 * @param string $remote_url URL to post to.
 *
 * @return The response or error.
 */
function jp_rest_client_remote_post( $post, $auth, $remote_url ){
	if ( is_object( $post ) ) {
		$headers    = array (
			'Authorization' => $auth,
		);

		$response = wp_remote_post( $remote_url, array (
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => $headers,
				'body'        => json_encode( $post ),
				'cookies'     => array ()
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo sprintf( '<p class="error">Something went wrong: %1s</p>', $error_message );
		}
		else {
			echo 'Response:<pre>';
			print_r( $response );
			echo '</pre>';
		}
	}
	else {
		$error_message = 'The input data was invalid.';
		echo sprintf( '<p class="error">Something went wrong: %1s</p>', $error_message );
	}

}