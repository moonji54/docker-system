<?php

/**
 * @file
 * Contains nrgi_frontend.node.inc.
 */

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Implements hook_preprocess_node().
 */
function nrgi_frontend_preprocess_node(&$variables): void {
  /** @var Drupal\node\NodeInterface $node */
  $node = $variables['node'];

  if ($node instanceof NodeInterface) {

    // Based on content type (applied to all view modes).
    _nrgi_frontend_preprocess_node_type_label($variables, $node);
    // _nrgi_frontend_preprocess_node_date($variables, $node);
    /** @var \Drupal\nrgi_frontend\Services\NrgiTranslationHelperService $translation_helper_service */
    $translation_helper_service = \Drupal::service('nrgi_frontend.translation_helper');
    /** @var \Drupal\nrgi_frontend\Services\MetadataHelperService $metadata_helper_service */
    $metadata_helper_service = \Drupal::service('nrgi_frontend.metadata_helper');

    switch ($node->bundle()) {
      case 'homepage':
        $variables['language_bar_links'] = $translation_helper_service->getLanguageSwitcherLinks(
          $node, TRUE, 'field_attached_translated_items'
        );
        break;

      case 'landing_page':
        $variables['language_switcher_links'] = $translation_helper_service->getLanguageSwitcherLinks(
          $node, TRUE, 'field_attached_translated_items'
        );
        break;

      case 'person':

        break;

      case 'article':
      case 'publication':
      case 'event':
        if (in_array($variables['view_mode'], ['full', 'default'])) {
          $metadata_helper_service->preprocessMetadata($node, $variables);
          $metadata_helper_service->preprocessSidebarMetadata($node, $variables);
          break;
        }
    }

    $view_mode_responsive_image_style = [
      'featured_content_with_image_1_per_row' => 'large_card',
      'featured_content_with_image_2_per_row' => 'medium_card',
      'featured_content_with_image_3_per_row' => 'small_card',
      'featured_content_with_image_4_per_row' => 'small_card',
      'featured_page_with_image_1_per_row' => 'large_card',
      'featured_page_with_image_2_per_row' => 'medium_card',
      'featured_page_with_image_3_per_row' => 'small_card',
      'featured_page_with_image_4_per_row' => 'small_card',
      'featured_people_with_image_1_per_row' => 'square_small',
      'featured_people_with_image_2_per_row' => 'square_small',
      'featured_people_with_image_3_per_row' => 'square_small',
      'featured_people_with_image_4_per_row' => 'square_small',
    ];

    // Based on view mode.
    switch ($variables['view_mode']) {
      case 'featured_content_with_image_1_per_row':
      case 'featured_content_with_image_2_per_row':
      case 'featured_content_with_image_3_per_row':
      case 'featured_content_with_image_4_per_row':
      case 'featured_content_1_per_row':
      case 'featured_content_2_per_row':
      case 'featured_content_3_per_row':
      case 'featured_content_4_per_row':
      case 'featured_page_with_image_1_per_row':
      case 'featured_page_with_image_2_per_row':
      case 'featured_page_with_image_3_per_row':
      case 'featured_page_with_image_4_per_row':
      case 'featured_people_with_image_1_per_row':
      case 'featured_people_with_image_2_per_row':
      case 'featured_people_with_image_3_per_row':
      case 'featured_people_with_image_4_per_row':
        if ($node->hasField('field_featured_image')
            && $node->get('field_featured_image')
            && $media = $node->get('field_featured_image')->entity) {
          if ($media instanceof MediaInterface && isset($view_mode_responsive_image_style[$variables['view_mode']])) {
            /** @var  \Drupal\nrgi_frontend\Services\NrgiResponsiveImageHelperService $responsive_image_style_service */
            $responsive_image_style_service = \Drupal::service('nrgi_frontend.responsive_image_helper');
            $responsive_image_style_service->preprocessResponsiveImage(
              $media,
              $view_mode_responsive_image_style[$variables['view_mode']],
              $variables
            );
          }
        }

        $content_types_to_preprocess = [
          'article',
          'career_opportunity',
          'event',
          'publication',
        ];

        // Preprocess card metadata.
        if (in_array($node->bundle(), $content_types_to_preprocess)) {
          $metadata_helper_service->preprocessCardMetadata($node, $variables);
        }
        break;

      case
      'full':
        // Functions only for full view mode.
        _nrgi_frontend_preprocess_node_meta($variables, $node);
        _nrgi_frontend_preprocess_node_person_featured_work($variables, $node);
        _nrgi_frontend_preprocess_node_related_content($variables);
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
function _nrgi_frontend_preprocess_node_type_label(array &$variables, NodeInterface $node): void {
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

/**
 * Preprocess related content.
 *
 * @param array $variables
 *   The variables from template_preprocess_node().
 */
function _nrgi_frontend_preprocess_node_related_content(array &$variables): void {
  /** @var \Drupal\node\Entity\Node $node */
  $node = $variables['node'];
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
function _nrgi_frontend_preprocess_node_person_featured_work(array &$variables, NodeInterface $node): void {
  if ($node->bundle() == 'person') {
    $view_mode = 'featured_content_with_image';
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

    $fields = ['field_author'];

    if ($node->hasField('field_limit')
        && !empty($node->get('field_limit')->getValue()[0])) {
      $limit = (int) $node->get('field_limit')->getValue()['0']['value'];
    }

    /** @var Drupal\nrgi_frontend\PersonFeaturedWorkHelperService $person_featured_work_helper_service */
    $person_featured_work_helper_service = \Drupal::service('nrgi_frontend.person_featured_work');
    $feature_work = $person_featured_work_helper_service->getPersonFeaturedWorkNodes($node, $limit, $allowed_types, $view_mode, $fields);
    $variables['featured_work'] = [
      'content' => $feature_work,
      'count' => count($feature_work),
    ];
  }
}
