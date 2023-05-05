<?php

/**
 * @file
 * Contains nrgi_frontend.node.inc.
 */

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\link\LinkItemInterface;
use Drupal\media\MediaInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements hook_preprocess_node().
 *
 * @throws \Drupal\Core\TypedData\Exception\MissingDataException
 * @throws \Drupal\Core\Entity\EntityMalformedException
 */
function nrgi_frontend_preprocess_node(&$variables): void {
  /** @var Drupal\node\NodeInterface $node */
  $node = $variables['node'];

  if ($node instanceof NodeInterface) {
    /** @var \Drupal\nrgi_frontend\Services\NrgiTranslationHelperService $translation_helper_service */
    $translation_helper_service = \Drupal::service('nrgi_frontend.translation_helper');
    /** @var \Drupal\nrgi_frontend\Services\MetadataHelperService $metadata_helper_service */
    $metadata_helper_service = \Drupal::service('nrgi_frontend.metadata_helper');

    // Adding direction manually, instead of language setting. On Drupal
    // settings, we set Arabic to LTR to avoid 'flipping' all the site.
    // We need to 'flip' only the node content (not header, footers, etc).
    $variables['language_direction'] = $node->language()->getId() == 'ar' ? 'rtl' : 'ltr';

    switch ($node->bundle()) {
      case 'homepage':
      case 'region':
        $variables['language_switcher_links'] = $translation_helper_service->getLanguageSwitcherLinks(
          $node, TRUE, 'field_attached_translated_items'
        );
        break;

      case 'page':
        // Add sub-navigation.
        _nrgi_frontend_preprocess_subnavigation($variables);
        $variables['language_switcher_links'] = $translation_helper_service->getLanguageSwitcherLinks(
          $node, FALSE, 'field_attached_translated_items'
        );
        break;

      case 'landing_page':
      case 'listing_page':
      case 'page':
        // Add sub-navigation.
        _nrgi_frontend_preprocess_subnavigation($variables);
        $variables['language_switcher_links'] = $translation_helper_service->getLanguageSwitcherLinks(
          $node, TRUE, 'field_attached_translated_items'
        );
        break;

      case 'person':
        $metadata_helper_service->preprocessMetadata($node, $variables);
        $variables['language_switcher_links'] = $translation_helper_service->getLanguageSwitcherLinks(
          $node, FALSE, 'field_attached_translated_items'
        );
        break;

      case 'topic':
      case 'country':
        $variables['language_switcher_links'] = $translation_helper_service->getLanguageSwitcherLinks(
          $node, FALSE, 'field_attached_translated_items'
        );
        break;

      case 'article':
      case 'publication':
      case 'career_opportunity':
      case 'event':
        if (in_array($variables['view_mode'], ['full', 'default'])) {
          $metadata_helper_service->preprocessMetadata($node, $variables);
          $metadata_helper_service->preprocessSidebarMetadata($node, $variables);
          _nrgi_frontend_preprocess_related_content($variables);
          break;
        }
    }

    $view_mode_responsive_image_style = [
      'featured_content_with_image_1_per_row' => 'large_card',
      'featured_content_with_image_2_per_row' => 'large_card',
      'featured_content_with_image_3_per_row' => 'medium_card',
      'featured_content_with_image_4_per_row' => 'small_card',
      'featured_page_with_image_1_per_row' => 'large_card',
      'featured_page_with_image_2_per_row' => 'large_card',
      'featured_page_with_image_3_per_row' => 'medium_card',
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

      case 'featured_content_1_per_row':
      case 'featured_content_2_per_row':
      case 'featured_content_3_per_row':
      case 'featured_content_4_per_row':
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

      case 'list_item_with_image':
        if ($node->hasField('field_featured_image')
            && $node->get('field_featured_image')
            && $media = $node->get('field_featured_image')->entity) {
          if ($media instanceof MediaInterface) {
            /** @var  \Drupal\nrgi_frontend\Services\NrgiResponsiveImageHelperService $responsive_image_style_service */
            $responsive_image_style_service = \Drupal::service('nrgi_frontend.responsive_image_helper');
            $responsive_image_style_service->preprocessResponsiveImage(
              $media,
              'small_card',
              $variables
            );
          }
        }
        $metadata_helper_service->preprocessListItemMetadata($node, $variables);
        break;

      case 'list_item':
        $metadata_helper_service->preprocessListItemMetadata($node, $variables);
        break;
    }
  }
}

/**
 * Implements hook_ENTITY_TYPE_view().
 *
 * @throws \Drupal\Core\TypedData\Exception\MissingDataException
 */
function nrgi_frontend_node_view(
  array &$build,
  EntityInterface $entity,
  EntityViewDisplayInterface $display,
  $view_mode
) {
  $resource_types = ['article', 'publication'];

  if ($entity instanceof NodeInterface
      && in_array($entity->bundle(), $resource_types)
      && in_array($view_mode, ['full', 'default'])) {

    // External link - node view disabled.
    if ($entity->hasField('field_link')
        && $external_link_field = $entity->get('field_link')) {
      if ($external_link = $external_link_field->first()) {
        if ($external_link instanceof LinkItemInterface) {
          $external_link_uri = $external_link->get('uri')->getValue();

          if ($external_link_uri) {
            if ($entity->hasField('field_disable')
                && $disable_field = $entity->get('field_disable')) {
              if ($disable_field->value) {
                // Redirect to the link.
                $response = new RedirectResponse($external_link_uri);
                $response->send();
              }
            }
          }
        }
      }
    }
  }
}

/**
 * Preprocess related content.
 *
 * @param array $variables
 *   The variables array.
 *
 * @throws \Drupal\Core\TypedData\Exception\MissingDataException
 */
function _nrgi_frontend_preprocess_related_content(array &$variables): void {
  /** @var \Drupal\node\NodeInterface $node */
  $node = $variables['node'];

  $disable_related_content = FALSE;

  if ($node->hasField('field_disable_related_content')
      && $disable_field = $node->get('field_disable_related_content')) {
    $disable_related_content = $disable_field->first()
                               && (bool) $disable_field->first()->value;
  }

  if (!$disable_related_content) {

    /** @var \Drupal\soapbox_related_content\RelatedContentHelperService $related_content_helper */
    $related_content_helper = Drupal::service('soapbox_related_content.related_content');

    // Which node types are allowed as related content results?
    $related_content_helper->setAllowedTargetNodeTypes([
      'article',
      'event',
      'publication',
    ]);

    // Which fields should be compared?
    $related_content_helper->setFieldsToCheck([
      'field_topic',
      'field_country',
      'field_region',
    ]);

    // Which view mode should be used to render the contents?
    $related_content_helper->setViewMode('featured_content_3_per_row');

    // Add manual selections.
    $manual_selections = [];
    if ($node->hasField('field_related_manual_content')) {
      $selections = $node->get('field_related_manual_content');

      foreach ($selections as $selection) {
        $manual_selections[] = $selection->target_id;
      }

      // Add manual selections.
      $related_content_helper->setManualSelections($manual_selections);
    }

    // Set the return limit.
    // A maximum of 4 automated related items, no limit for manual.
    // (flexibility to accommodate migrated manual related content)
    $return_limit = max(3, count($manual_selections));

    // Set the sort order. Unified date descending is default,
    // so this can be skipped unless you need something specific.
    $related_content_helper->setSortOrders([
      [
        'field' => 'unified_date',
        'direction' => 'DESC',
      ],
    ]);

    // How many related contents should we find?
    $related_content_helper->setReturnLimit($return_limit);

    // Run preprocess.
    $related_content_helper->preprocessNode($variables);
  }
}

/**
 * Preprocess sub navigation.
 *
 * @param array $variables
 *   The variables array.
 *
 * @throws \Drupal\Core\Entity\EntityMalformedException
 */
function _nrgi_frontend_preprocess_subnavigation(array &$variables): void {
  /** @var \Drupal\soapbox_nodes\SubPagesHelperService $sub_pages_helper */
  $sub_pages_helper = \Drupal::service('soapbox_nodes.sub_pages');
  $sub_page_ids = $sub_pages_helper->getSubPages($variables['node'], 'main');

  if ($sub_page_ids) {
    foreach ($sub_page_ids as $page_node_id) {
      $sub_node = Node::load($page_node_id);

      if ($sub_node instanceof NodeInterface) {
        $sub_page_url = $sub_node->toUrl()->toString();
        $variables['sub_pages'][] = [
          'title' => $sub_node->label(),
          'url' => $sub_page_url,
        ];
      }
    }

    // Add menu cache tag.
    $variables = array_merge_recursive($variables, [
      '#cache' => [
        'tags' => ['config:system.menu.main'],
      ],
    ]);
  }
}

/**
 * Implements hook_soapbox_nodes_subpages_alter().
 */
function nrgi_frontend_soapbox_nodes_subpages_alter(&$show_sub_pages, &$show_sibling_pages): void {
  $show_sub_pages = TRUE;
  $show_sibling_pages = FALSE;
}

/**
 * Implements hook_entity_build_defaults_alter().
 */
function nrgi_frontend_entity_build_defaults_alter(array &$build, EntityInterface $entity, $view_mode): void {
  $listing_views = [
    'list_item',
    'list_item_with_image',
  ];

  if ($entity instanceof NodeInterface && !isset($entity->in_preview) && in_array($view_mode, $listing_views)) {
    // Add cache key to nodes showed in listings.
    // The cache should be invalidated if the view display is different as
    // the node rendering depends on view display (with/without filters/images).
    $build['#cache']['keys'][] = $entity->view->current_display;
  }

}
