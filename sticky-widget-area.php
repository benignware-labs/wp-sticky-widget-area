<?php

/**
 Plugin Name: Sticky Widget Area
 Plugin URI: http://github.com/benignware/wp-sticky-widget-area
 Description: Make widget-areas sticky
 Version: 0.0.8
 Author: Rafael Nowrotek, Benignware
 Author URI: http://benignware.com
 License: MIT
*/

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
  wp_enqueue_script( 'sticky-widget-area-js', plugin_dir_url( __FILE__ ) . 'dist/sticky-widget-area.js' );
  wp_enqueue_style( 'sticky-widget-area-style', plugin_dir_url( __FILE__ ) . 'dist/sticky-widget-area.css' );
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
  @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $output );

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
      $sidebar_element->setAttribute('data-sticky-widget-area-options', urlencode(json_encode($options)));

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
