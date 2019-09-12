<?php

/**
 Plugin Name: Sticky Widget Area
 Plugin URI: http://github.com/benignware/wp-sticky-widget-area
 Description: Make widget-areas sticky
 Version: 0.0.13
 Author: Rafael Nowrotek, Benignware
 Author URI: http://benignware.com
 License: MIT
*/

require_once 'lib/functions.php';

function wp_sticky_widget_area_is_admin() {
  //Ajax request are always identified as administrative interface page
  //so let's check if we are calling the data for the frontend or backend
  if (wp_doing_ajax()) {
    $adminUrl = get_admin_url();
    //If the referer is an admin url we are requesting the data for the backend
    return (substr($_SERVER['HTTP_REFERER'], 0, strlen($adminUrl)) === $adminUrl);
  }

  //No ajax request just use the normal function
  return is_admin();
}

// Enqueue plugin scripts
add_action('wp_enqueue_scripts', function() {
  $options = get_sticky_widget_area_options();

  wp_register_script( 'sticky-widget-area-js', plugin_dir_url( __FILE__ ) . 'dist/sticky-widget-area.js' );
  wp_localize_script( 'sticky-widget-area-js', 'StickyWidgetArea',
    array(
      'options' => json_encode($options),
    )
  );

  wp_enqueue_script( 'sticky-widget-area-js' );

  wp_register_style( 'sticky-widget-area-style', false );
  wp_enqueue_style( 'sticky-widget-area-style' );

  $css_file = dirname(__FILE__) . '/dist/sticky-widget-area.css';
  $css = file_get_contents($css_file);
  $minWidth = $options['minWidth'];

  if ($css && $minWidth) {
    $css = '@media screen and (min-width: ' . $minWidth . 'px) { ' . $css . ' }';

    wp_add_inline_style('sticky-widget-area-style', $css );
  }
});

add_action( 'get_sidebar', function($widget_id = null) {
  if (!wp_sticky_widget_area_is_admin()) {
    echo "<span data-sticky-widget-area-entry></span>";
  }
});

function sticky_widget_area_filter_output($output) {
  if (wp_sticky_widget_area_is_admin()) {
    return $output;
  }

  $options = apply_filters('sticky_widget_area_options', array(
    // Default options
  ));

  // Parse DOM
  $doc = new DOMDocument();
  libxml_use_internal_errors(true);
  @$doc->loadHTML('<?xml encoding="UTF-8">' . $output);
  libxml_clear_errors();

  // dirty fix
  foreach ($doc->childNodes as $item)
    if ($item->nodeType == XML_PI_NODE)
        $doc->removeChild($item); // remove hack
  $doc->encoding = 'UTF-8'; // insert proper

  $doc_xpath = new DOMXpath($doc);
  $elements = $doc_xpath->query('//*[@data-sticky-widget-area-entry]');

  if ($elements->length === 0) {
    return $output;
  }

  foreach ($elements as $element) {
    $next_element = null;
    $next_sibling = $element->nextSibling;
    $previous_sibling = $element->previousSibling;

    while ($next_sibling) {
      if ($next_sibling->nodeType === 1) {
        $next_element = $next_sibling;
        break;
      } else {
        $next_sibling = $next_sibling->nextSibling;
      }
    }

    while ($previous_sibling) {
      if ($previous_sibling->nodeType === 1) {
        $previous_element = $previous_sibling;
        break;
      } else {
        $previous_sibling = $previous_sibling->previousSibling;
      }
    }

    if ($next_element && $previous_element) {
      $sidebar_element = $next_element;
      $content_element = $previous_element;

      $sidebar_element->setAttribute('data-sticky-widget-area-role', 'sidebar');
      $content_element->setAttribute('data-sticky-widget-area-role', 'content');

      // Create inner element
      $inner_element = $doc->createElement('div');
      $inner_element->setAttribute('data-sticky-widget-area-role', 'sidebar-inner');

      // Copy children
      while($sidebar_element->firstChild) {
        $inner_element->appendChild($sidebar_element->firstChild);
      }

      $sidebar_element->appendChild($inner_element);

      // Create container element
      $container_element = $doc->createElement('div');
      $container_element->setAttribute('data-sticky-widget-area-role', 'container');

      $content_element->parentNode->insertBefore($container_element, $content_element);
      $container_element->appendChild($content_element);
      $container_element->appendChild($sidebar_element);
    }

    $element->parentNode->removeChild($element);
  }

  $output = $doc->saveHTML();
  $output = str_replace('<?xml encoding="utf-8" ?>', '', $output);

  return $output;
}

add_action('after_setup_theme', function() {
  if (!wp_sticky_widget_area_is_admin()) {
    // Start observing buffer
    ob_start("sticky_widget_area_filter_output");
  }
});

add_action('shutdown', function() {
  if (!wp_sticky_widget_area_is_admin()) {
    // Flush buffer
    ob_end_flush();
  }
});
