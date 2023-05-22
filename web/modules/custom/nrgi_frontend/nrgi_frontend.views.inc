<?php

/**
 * @file
 * Provides additional variables for the views templates.
 */

use Drupal\Core\Form\FormState;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_form_views_exposed_form_alter().
 */
function nrgi_frontend_form_views_exposed_form_alter(&$form, &$form_state) {
  if (!empty($form['keyword']['#type']) && $form['keyword']['#type'] == 'entity_autocomplete') {
    array_unshift($form['#validate'], '_nrgi_frontend_delete_keyword_errors');

  }
}

/**
 * Implements template_preprocess_views_view.
 *
 * @param array $variables
 *   Preprocessing variables.
 */
function nrgi_frontend_preprocess_views_view(array &$variables) {
  $view = $variables['view'];
  if ($view instanceof ViewExecutable && $view->id() == 'search_view') {
    // Get the exposed filters.
    $exposed = $view->getExposedInput();

    // Get search keyword.
    if (!empty($exposed['query'])) {
      $variables['query'] = $exposed['query'];
    }
  }
}

/**
 * Remove keyword autocomplete field errors from form errors.
 *
 * @param array $form
 *   The form.
 * @param \Drupal\Core\Form\FormState $form_state
 *   The form state.
 */
function _nrgi_frontend_delete_keyword_errors(array $form, FormState &$form_state) {
  $form_elements = $form_state->getValues();

  if ($form_elements['keyword'] == NULL) {
    // Temporarily store all form errors.
    $form_errors = $form_state->getErrors();

    // Clear the form errors.
    $form_state->clearErrors();

    // Remove the keyword form error.
    unset($form_errors['keyword']);

    // Now loop through and re-apply the remaining form error messages.
    foreach ($form_errors as $name => $error_message) {
      $form_state->setErrorByName($name, $error_message);
    }

  }
}

/**
 * Implements hook_views_query_alter().
 */
function nrgi_frontend_views_query_alter(ViewExecutable $view, QueryPluginBase $query) {
  // Exclude 404, 404 and homepage from search result.
  $page_config = \Drupal::config('system.site')->get('page');
  $pages_to_exclude = ['403', '404', 'front'];
  if ($view->id() === 'search_view') {
    foreach ($pages_to_exclude as $page) {
      if (!empty($page_config[$page])) {
        $exploded = explode('/', $page_config[$page]);
        if (!empty($exploded)) {
          $excluded_nids[] = $exploded[2];
        }
      }
    }
    if (!empty($excluded_nids)) {
      // Remove the node ids from the search index query.
      $fields = $query->getIndex()->getFields();
      if (!empty($fields['nid'])) {
        $query->addCondition('nid', $excluded_nids, 'NOT IN');
      }
    }
  }
}
