<?php
/**
 * @file
 * Provides additional variables for the views templates.
 */

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_data_alter().
 */
function project_frontend_views_data_alter(array &$data) {

  // On the 'node_field_data' add a new custom filter for unified_date_year.
  $data['node_field_data']['unified_date_year'] = [
    'title'  => t('Unified Date Year'),
    'filter' => [
      'title' => t('Unified Date Year'),
      'help'  => t('Provide a list of years to filter by.'),
      'field' => 'unified_date',
      'id'    => 'unified_date_year',
    ],
  ];

  // Auto make unified date year available in search_api_*.
  foreach ($data as $source => $settings) {
    if (strpos($source, 'search_api') === 0) {
      $data[$source]['unified_date_year'] = $data['node_field_data']['unified_date_year'];
    }
  }
}

/**
 * Implements hook_views_query_alter().
 */
function project_frontend_views_query_alter(ViewExecutable $view, QueryPluginBase $query) {
  // Exclude 404, 404 and homepage from search result.
  $page_config      = \Drupal::config('system.site')->get('page');
  $pages_to_exclude = ['403', '404', 'front'];

  if ($view->id() === 'search') {
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
