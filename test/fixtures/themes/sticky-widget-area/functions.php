<?php


add_action('wp_enqueue_scripts', function() {
  wp_deregister_style( 'twentyseventeen-style');
  wp_register_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  wp_enqueue_style('twentyseventeen-style', get_template_directory_uri(). '/style.css');
  wp_enqueue_style( 'childtheme-style', get_stylesheet_directory_uri().'/style.css', array('twentyseventeen-style') );
});


add_filter('sticky_widget_area_options', function($options = array()) {
	$options = array_merge($options, array(
		'resizeSensor' => true,
		'topSpacing' => 82,
		'bottomSpacing' => 0,
		'minWidth' => 992
	));

	return $options;
});
