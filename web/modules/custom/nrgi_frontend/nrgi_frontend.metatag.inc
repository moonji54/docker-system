<?php

/**
 * @file
 * Contains nrgi_metatag.inc.
 */

use Drupal\image\Entity\ImageStyle;
use Drupal\node\NodeInterface;

/**
 * Implements hook_metatags_alter().
 */
function nrgi_frontend_metatags_alter(array &$metatags, array &$context) {
  // Fallback images logic implementation is needed via this hook
  // for Twitter card image metatags (does not recognise multiple
  // fields on admin UI.)
  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node instanceof NodeInterface) {
    if ($node->hasField('field_featured_image') && $node->get('field_featured_image')->target_id) {
      $metatag_image = '[node:field_featured_image:entity:field_media_image:og_image:url]';
    }
    else {
      $metatag_image = '[site:url]themes/custom/nrgi/assets/img/fallback-default.png';

      $og_image_style = ImageStyle::load('og_image');
      if ($og_image_style instanceof ImageStyle) {
        if ($og_image_style->getEffects()
            && $scale_crop_data = $og_image_style->getEffects()
              ->getConfiguration(
              'image_scale_and_crop'
            )) {

          $scale_crop_data = reset($scale_crop_data);
          $fallback_height = $scale_crop_data['data']['height'] ?? '';
          $fallback_width = $scale_crop_data['data']['width'] ?? '';
        }
      }
      $metatags["og_image_width"] = $metatags["twitter_cards_image_width"] = $fallback_width ?? '1024';
      $metatags["og_image_height"] = $metatags["twitter_cards_image_height"] = $fallback_height ?? '576';
    }
    $metatags["image_src"] = $metatags["twitter_cards_image"] = $metatag_image;
  }
}
