<?php

/**
 * @file
 * Contains nrgi_backend.theme.inc.
 */

/**
 * Include the custom admin css.
 *
 * @param array $variables
 *   Variables to preprocess.
 */
function nrgi_backend_preprocess_page(array &$variables) {
  if (\Drupal::service('router.admin_context')->isAdminRoute()) {
    $variables['#attached']['library'][] = 'nrgi_backend/nrgi-backend-styling';
  }
}
