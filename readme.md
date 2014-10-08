JP WP REST API Client
=====================

A simple client for creating and updating posts via the WordPress REST API via the WordPress HTTP API. Most of this code is based on articles I wrote for [Torque](http://torquemag.io/author/joshp/).

###Usage
* Get A Post
```php
    //get url string for posts in multiple CPTs with several filters
    $filters = array(
    	'posts_per_page' => 50,
    	'orderby' => 'modified_gmt',
    	'offset' => '10'
    );

    $post_types = array(
    	'post',
    	'page',
    	'book',
    	'author'
    );

    $url = JP_WP_REST_API_Client::posts_url_string( $post_types, $filters );

    //make request
    $response = JP_WP_REST_API_Client::get_json( $url );
```

* Get A Post Leveraging Cache
```php
    //get url string for posts in multiple CPTs with several filters
    $filters = array(
    	'posts_per_page' => 50,
    	'orderby' => 'modified_gmt',
    	'offset' => '10'
    );

    $post_types = array(
    	'post',
    	'page',
    	'book',
    	'author'
    );

    $url = JP_WP_REST_API_Client::posts_url_string( $post_types, $filters );

    //make request
    $response = JP_WP_REST_API_Client::get_json_cached( $url );
```

* Get All The Posts Leveraging Cache
`$posts = JP_WP_REST_API_Client::get_json_cached( json_url() );```

* Copy Post From Remote Site To Local Site
```php
    $url = 'http://remote_site.com/wp-json/5';

    //get post
    $post = JP_WP_REST_API_Client::get_json( $url );

    //insert to current site
    $new_post_id = JP_WP_REST_API_Client::insert_post_from_json( $post );

```

* Copy Post From Local Site To Remote Site

```php
    $url = json_url( 'posts/1' );

    //get post
    $post = JP_WP_REST_API_Client::get_json( $url );

    //make sure we got JSON
    if ( is_string( $post ) ) {
        //setup auth using basic auth
        $auth = 'Basic ' . base64_encode( 'username' . ':' . 'password' );
        $response = JP_WP_REST_API_Client::remote_post( $post, $auth, 'http://remotesite.com/wp-json/posts' );
        if ( ! is_wp_error( $response ) {
            //do something with correct response
        }
    }
```

### License
Copyright 2014 Josh Pollock. Licensed under the terms of the GNU General public license version 2. Please share with your neighbor.


