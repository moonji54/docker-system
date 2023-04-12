<?php

/**
 * @file
 * Provides custom theme suggestions.
 */

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
