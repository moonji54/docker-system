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

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function nrgi_frontend_theme_suggestions_input_alter(&$suggestions, array $variables) {
  $element = $variables['element'];
  if (isset($element['#attributes']['data-twig-suggestion'])) {
    // Adding the theme suggestion "manually" for form field element (submit)
    // that have the data-twig-suggestion attribute set.
    $suggestion_suffix = str_replace(['-'], '_', $element['#attributes']['data-twig-suggestion']);
    $suggestions[] = 'input__' . $element['#type'] . '__' . $suggestion_suffix;
  }
}
