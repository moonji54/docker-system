<?php

/**
 * @file
 * Contains nrgi_backend.theme.inc.
 */

/**
 * Implements hook_preprocess_page().
 */
function nrgi_backend_preprocess_page(array &$variables) {
  $variables['#attached']['library'][] = 'nrgi_backend/nrgi-backend-style';
}
