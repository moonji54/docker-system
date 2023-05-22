<?php

/**
 * @file
 * Provides custom theme suggestions.
 */

/**
 * Implements hook_theme_suggestions_HOOK().
 */
function nrgi_frontend_theme_suggestions_node_alter(array &$suggestions, array $variables) {
  if (isset($variables['elements']['#view_mode'])) {
    $suggestions[] = 'node__' . $variables['elements']['#view_mode'];
  }

  $nodeType = $variables['elements']['#node']->bundle();
  if ($nodeType == 'initiative' || $nodeType == 'theme') {
    $contentType = $variables['elements']['#node']->get('field_type')->value;
    $suggestions[] = 'node__' . $nodeType . '_' . $contentType;
  }

  $node_paths = \Drupal::config('system.site')->get('page');
  $node_404_id = str_replace('/node/', '', $node_paths['404']);
  $node_403_id = str_replace('/node/', '', $node_paths['403']);
  if (in_array('node__' . $node_404_id, $suggestions)) {
    $suggestions[] = 'node__404';
  }
  elseif (in_array('node__' . $node_403_id, $suggestions)) {
    $suggestions[] = 'node__403';
  }

}

/**
 * Implements hook_theme_suggestions_HOOK().
 */
function nrgi_frontend_theme_suggestions_form_element_alter(
  array &$suggestions,
  array $variables,
  $hook
): void {
  if (in_array($variables['element']['#type'], [
    'textfield',
    'entity_autocomplete',
  ])) {
    $suggestions[] = $hook . '__text_or_auto_complete';
  }
}
