<?php

/**
 * @file
 * Contains project_frontend.node.inc.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;

/**
 * Implements hook_preprocess_node().
 */
function project_frontend_preprocess_node(&$variables) {
  /** @var Drupal\node\NodeInterface $node */
  $node = $variables['node'];

  if ($node && $node instanceof NodeInterface) {
    _project_frontend_preprocess_node_type_label($variables, $node);
    // _project_frontend_preprocess_node_date($variables, $node);

    // Based on content type (applied to all view modes).
    switch ($node->bundle()) {
      case 'person':

        break;
    }

    // Based on view mode.
    switch ($variables['view_mode']) {
      case 'card':

        break;

      case 'full':
        // Functions only for full view mode.
        _project_frontend_preprocess_node_meta($variables, $node);
        _project_frontend_preprocess_node_person_featured_work($variables, $node);
        _project_frontend_preprocess_node_related_content($variables);
        break;

      case 'person_card':
        // Function for person_card.

        break;
    }
  }
}

/**
 * Prepare the Content Type / Sub Type label to be shown on the frontend.
 *
 * @param array $variables
 *   The variables from template_preprocess_node().
 * @param \Drupal\node\NodeInterface $node
 *   Node to preprocess.
 *
 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
 * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
 */
function _project_frontend_preprocess_node_type_label(array &$variables, NodeInterface $node) {
  // Content Type.
  $variables['type_label'] = $node->type->entity->label();

  // Sub Type.
  if ($node->hasField('field_subtype')) {
    $tid = $node->get('field_subtype')->getString();

    if (!empty($tid)) {
      $term = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->load($tid);

      if ($term instanceof Term) {
        $variables['type_label'] = $term->label();
      }
    }
  }
}

///**
// * @param array $variables
// *   The variables from template_preprocess_node().
// * @param \Drupal\node\NodeInterface $node
// *   Node to preprocess.
// */
// function _project_frontend_preprocess_node_date(array &$variables, NodeInterface $node) {
//  /** @var \Drupal\Core\Field\FieldItemList $unified_date_field */
//  $unified_date_field = $node->get('unified_date');
//  $date_timestamp     = $unified_date_field->getString();
//  $date_string        = FALSE;
//
//  $bundle   = $node->bundle();
//  $template = $node->get('template')->getString();
//
//  if ($template === 'external_item') {
//    if ($node->hasField('field_date')) {
//      if (!empty($node->get('field_date')->getString())) {
//        $date_field = $node->get('field_date');
//
//        /** @var DrupalDateTime $date */
//        $date           = $date_field->date;
//        $date_timestamp = $date->getTimestamp();
//      }
//    }
//  }
//
//  if ($template === 'collection_item' || $bundle === 'event') {
//    if ($node->hasField('field_start_date')) {
//      if (!empty($node->get('field_start_date')->getString())) {
//        $date_field = $node->get('field_start_date');
//
//        /** @var DrupalDateTime $date */
//        $date           = $date_field->date;
//        $date_timestamp = $date->getTimestamp();
//      }
//    }
//  }
//
//  if (!empty($date_timestamp)) {
//    if (is_string($date_timestamp)) {
//      $date_timestamp = intval($date_timestamp);
//    }
//
//    $date_string     = \Drupal::service('date.formatter')->format($date_timestamp, 'project_date');
//    $date_string_ymd = \Drupal::service('date.formatter')->format($date_timestamp, 'html_date');
//  }
//
//  $variables['node_date']      = $date_string;
//  $variables['node_html_date'] = $date_string_ymd;
//}


/**
 * Preprocess related content.
 *
 * @param array $variables
 *   The variables from template_preprocess_node().
 */
function _project_frontend_preprocess_node_related_content(array &$variables) {
  /** @var \Drupal\node\Entity\Node $node */
  $node  = $variables['node'];
  $limit = 4;

  if ($node->hasField('field_limit')) {
    if (!empty($node->get('field_limit')->getValue()[0])) {
      $limit = (int) $node->get('field_limit')->getValue()['0']['value'];
    }

    // SBTODO: Configure the related content types and fields below.

    /** @var Drupal\soapbox_related_content\RelatedContentHelperService $related_content_helper_service */
    $related_content_helper_service = Drupal::service('soapbox_related_content.related_content');
    $related_content_helper_service->setAllowedTargetNodeTypes([
      'article',
      'report',
      'external_publication',
      'event',
      'media_item',
      'collection',
      'series',
    ]);

    $related_content_helper_service->setCrossReferenceFieldsToCheck([
      'field_collection',
      'field_author',
      'field_project',
      'field_series',
      'field_speakers',
    ]);

    $related_content_helper_service->setFieldsToCheck([
      'field_collection',
      'field_author',
      'field_project',
      'field_series',
      'field_theme',
      'field_keyword',
      'field_country',
    ]);

    $manual_selections = [];

    if ($node->hasField('field_related_manual_content')) {
      $selections = $node->get('field_related_manual_content');

      foreach ($selections as $selection) {
        $manual_selections[] = $selection->target_id;
      }

      // Add manual selections.
      $related_content_helper_service->setManualSelections($manual_selections);
    }

    $related_content_helper_service->setViewMode('card');
    $related_content_helper_service->setReturnLimit($limit);
    $related_content_helper_service->preprocessNode($variables);
  }
}

/**
 * Preprocess person featured work content.
 *
 * @param array $variables
 *   The variables from the template_preprocess_node().
 * @param \Drupal\node\NodeInterface $node
 *   Node to preprocess.
 */
function _project_frontend_preprocess_node_person_featured_work(array &$variables, NodeInterface $node) {
  if ($node->bundle() == 'person') {
    $view_mode     = 'featured_content_with_image';
    $allowed_types = [
      'article',
      'collection',
      'event',
      'external_publication',
      'media_item',
      'report',
      'series',
    ];

    $limit = 4;

    if ($node->hasField('field_limit') && !empty($node->get('field_limit')->getValue()[0])) {
      $limit = (int) $node->get('field_limit')->getValue()['0']['value'];
    }

    /** @var Drupal\project_frontend\PersonFeaturedWorkHelperService $person_featured_work_helper_service */
    $person_featured_work_helper_service = \Drupal::service('project_frontend.person_featured_work');
    $feature_work                        = $person_featured_work_helper_service->getPersonFeaturedWorkNodes($node, $limit, $allowed_types, $view_mode);
    $variables['featured_work']          = [
      'content' => $feature_work,
      'count'   => count($feature_work),
    ];
  }
}
