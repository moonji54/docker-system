<?php

/**
 * @file
 * Contains project_backend.theme.inc.
 */

/**
 * Include the custom admin css.
 *
 * @param array $variables
 *   Variables to preprocess.
 */
function project_backend_preprocess_page(array &$variables) {
  if (\Drupal::service('router.admin_context')->isAdminRoute()) {
    $variables['#attached']['library'][] = 'project_backend/project-backend-styling';
  }
}
