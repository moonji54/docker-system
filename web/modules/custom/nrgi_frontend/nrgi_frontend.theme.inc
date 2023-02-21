<?php

/**
 * @file
 * Hooks related to theme and custom render arrays.
 */

/**
 * Implements hook_theme().
 */
function nrgi_frontend_theme($existing, $type, $theme, $path) {
  return [
    'nrgi_search_form' => [
      'render element' => 'search_form',
    ],
  ];
}
