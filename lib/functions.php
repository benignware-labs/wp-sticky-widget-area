<?php

function get_sticky_widget_area_options() {
  global $wp_registered_sidebars;

  $options = apply_filters('sticky_widget_area_options', array(
    /*
    'selector' => implode(',', array_slice(array_map(function($id) {
      return '#' . $id;
    }, array_keys(array_map(function($item) {
      return $item['id'];
    }, $wp_registered_sidebars))), 0, 1))
    */
    'selector' => "*[data-sticky-widget-area-role='sidebar']"
  ));

  return $options;
}
