<?php

/**
 * @file
 * Contains nrgi_frontend.node.inc.
 */

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

/**
 * Implements hook_preprocess_node().
 */
function nrgi_frontend_preprocess_node(&$variables): void {
  /** @var Drupal\node\NodeInterface $node */
  $node = $variables['node'];

  if ($node instanceof NodeInterface) {
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

    // Card view modes.
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

    }
  }
}
