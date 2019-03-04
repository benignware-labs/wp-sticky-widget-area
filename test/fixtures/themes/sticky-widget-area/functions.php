<?php


add_action( 'wp_enqueue_scripts', function() {
  wp_deregister_style( 'twentyseventeen-style');
  wp_deregister_script( 'twentyseventeen-script');
  wp_register_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  //wp_enqueue_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  wp_enqueue_style( 'childtheme-style', get_stylesheet_directory_uri().'/style.css', array('twentyseventeen-style') );
} );

add_filter( 'alphabetic_paginate_links', function($link = '', $term = null) {
  if (!$term) {
    return $link;
  }

  $post_type = get_post_type();
  $post_id = get_the_ID();

  $sector = get_queried_object()->name;

  if (alphabetic_is_enabled($post_type)) {
    $taxonomy = alphabetic_get_taxonomy($post_type);

    $url = parse_url($link);
    $id = 'glossary-term-' . $term;

    $link = get_post_type_archive_link($post_type);
    $link = $link . '#' . $id;

    return $link;
  }

  return $link;
}, 2, 10);


add_filter('sticky_widget_area_options', function($options = array()) {
	$options = array_merge(array(
		'topSpacing' => 72
	), $options);
	return $options;
});
