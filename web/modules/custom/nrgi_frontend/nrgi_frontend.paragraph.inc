<?php

/**
 * @file
 * Contains nrgi_frontend.paragraph.inc.
 */

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

/**
 * Implements hook_preprocess_paragraph().
 */
function nrgi_frontend_preprocess_paragraph(&$variables) {
  // SBTODO - TODO: review and refactor function.
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $node = $paragraph->getParentEntity();

  // Adding node information as a render variable to paragraphs.
  $variables['node'] = $node;

  // Add details for specific paragraph types.
  switch ($paragraph->getType()) {
    case 'featured_content':
      // nrgi_frontend_preprocess_featured_content($paragraph, $variables);
      break;

    case 'featured_pages':
      /** @var \Drupal\nrgi_frontend\NrgiFeaturedPagesHelperService $paragraph_featured_pages_service */
      $paragraph_featured_pages_service = \Drupal::service('nrgi_frontend.paragraph_featured_pages_helper');

      // Preprocess featured pages.
      $paragraph_featured_pages_service->preprocessFeaturedPages($variables);
      break;

    case 'call_to_action':
    case 'learning_block':
      /** @var \Drupal\nrgi_frontend\NrgiParagraphButtonLinkHelperService $paragraph_button_link_helper_service */
      $paragraph_button_link_helper_service = \Drupal::service('nrgi_frontend.paragraph_button_link_helper');

      $variables['button_link'] = $paragraph_button_link_helper_service
        ->getButtonLinkArray(
          $paragraph,
          'field_link',
          'field_file',
          'field_download_button_label',
          'field_type'
        );
  }
}

/**
 * Preprocess featured content.
 *
 * @param \Drupal\paragraphs\Entity\Paragraph $paragraph
 *   THe paragraph entity.
 * @param array|object $variables
 *   The preprocess hook variables.
 */
//function _nrgi_frontend_preprocess_featured_content(Paragraph $paragraph, &$variables) {
//  /** @var \Drupal\soapbox_featured_cards\FeaturedCardsHelperService $featured_content_service */
//  $featured_content_service = \Drupal::service('soapbox_featured_cards.featured_cards');
//  // Sets image toggle field.
//  $featured_content_service->setImageToggleField($field = 'field_show_image');
//
//  // Flexible Publishing Model: Get current template.
//  $current_template = $paragraph->getParentEntity()->get('template')->value;
//  // Page builder field name per template.
//  $page_builder_field = 'field_page_builder_' . $current_template;
//  // Set the page builder field.
//  $featured_content_service->setPageBuilderField($page_builder_field);
//
//  // Sets the allowed view modes.
//  $featured_content_service->setViewModes([
//    'card'            => 'featured_content',
//    'card_with_image' => 'featured_content_with_image',
//  ]);
//
//  // Sets the manual selection field.
//  $featured_content_service->setManualSelectionFields('node',
//    'field_contents');
//
//  // Date filter.
//  $date_filter = '';
//  $date_fields = [];
//  if ($paragraph->hasField('field_events_to_show') &&
//    in_array($paragraph->get('field_events_to_show')->value,
//      [
//        'upcoming',
//        'past',
//      ])) {
//    $date_filter = $paragraph->get('field_events_to_show')->value;
//    $date_fields = ['event' => 'field_start_date'];
//  }
//
//  // Sets automated settings - allowed node types, taxonomy filters,
//  // field limit, sort fields.
//  $featured_content_service->setAutomatedSettings(
//    'field_content_types',
//    [
//      'field_theme',
//      'field_project',
//      'field_keyword',
//      'field_project',
//      'field_content_label',
//    ],
//    'field_limit',
//    [
//      'unified_date',
//    ],
//    $date_filter,
//    $date_fields,
//    'field_content_label'
//  );
//
//  // Allowed types to use when not defined by paragraph.
//  $allowed_types = [
//    'article',
//    'collection',
//    'event',
//    'external_publication',
//    'media_item',
//    'report',
//    'series',
//  ];
//
//  // Set paragraph reference.
//  $featured_content_service->setParagraph($paragraph);
//
//  // Set allowed types.
//  if (
//    $paragraph->hasField('field_node_bundles')
//    &&
//    ($values = $paragraph->get('field_node_bundles')->getValue())
//  ) {
//    $values        = reset($values);
//    $allowed_types = [$values['value']] ?? $allowed_types;
//
//  }
//  $featured_content_service->setAllowedTypes($allowed_types);
//
//  // Preprocess featured content.
//  $featured_content_service->preprocessFeaturedCards($variables);
//}
