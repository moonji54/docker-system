<?php

/**
 * @file
 * Contains nrgi_frontend.form.inc.
 */

/**
 * Close details form elements even if there is a selected option inside it.
 *
 * Implements hook_preprocess_details().
 */
function nrgi_frontend_preprocess_details(&$variables) {

  if (array_key_exists('open', $variables['attributes'])) {
    unset($variables['attributes']['open']);
  }

  /** @var \Drupal\Core\Template\Attribute $summary_attributes */
  $summary_attributes = $variables['summary_attributes'];

  $summary_attributes->setAttribute('aria-expanded', 'false');
  $summary_attributes->setAttribute('aria-pressed', 'false');
}
