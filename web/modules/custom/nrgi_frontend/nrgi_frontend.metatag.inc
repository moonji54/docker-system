<?php

/**
 * @file
 * Contains nrgi_frontend.metatag.inc.
 */

use Drupal\node\NodeInterface;

/**
 * Implements hook_metatags_alter().
 */
function nrgi_frontend_metatags_alter(array &$metatags, array &$context): void {
  $node = \Drupal::routeMatch()->getParameter('node');

  if ($node && $node instanceof NodeInterface) {
    switch ($node->bundle()) {
      case 'homepage':
        if ($node->hasField('field_background_image') && $node->get('field_background_image')->target_id) {
          $metatags["og_image"] = '[node:field_background_media:entity:field_media_image:og_image:url]';
          $metatags["twitter_cards_image"] = '[node:field_background_media:entity:field_media_image:og_image:url]';
        }
        else {
          $metatags["og_image"] = '[site:url]themes/custom/nrgi/assets/img/default-social-share.png';
          $metatags["twitter_cards_image"] = '[site:url]themes/custom/nrgi/assets/img/default-social-share.png';
        }
        break;

      default:
        if ($node->hasField('field_image') && $node->get('field_image')->target_id) {
          $metatags["og_image"] = '[node:field_image:entity:field_media_image:og_image:url]';
          $metatags["twitter_cards_image"] = '[node:field_image:entity:field_media_image:og_image:url]';
        }
        else {
          $metatags["og_image"] = '[site:url]themes/custom/nrgi/assets/img/default-social-share.png';
          $metatags["twitter_cards_image"] = '[node:field_image:entity:field_media_image:og_image:url]';
        }
        break;
    }
  }
}
