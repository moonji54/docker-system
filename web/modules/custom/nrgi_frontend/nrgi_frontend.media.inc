<?php

/**
 * @file
 * Media hooks.
 */

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

/**
 * Implements hook_preprocess_media().
 */
function nrgi_frontend_preprocess_media(&$variables) {
  $node = \Drupal::routeMatch()->getParameter('node');
  if ($node instanceof NodeInterface) {
    // If node's route is controlled by soapbox_pdf (pdf view mode).
    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($route_name == 'soapbox_pdf.node_pdf_page_controller') {
      $variables['is_pdf'] = TRUE;

      $media = $variables['elements']['#media'];
      if ($media instanceof MediaInterface) {
        $fallback_text_link = [
          'remote_video' => t('To watch the video, visit') . ' ',
          'remote_audio' => t('To listen to this audio, visit') . ' ',
          'data_visualisation' => t('To see this content, visit') . ' ',
        ];
        $variables['fallback_text_link'] = $fallback_text_link[$media->bundle()] ?? NULL;
        $variables['fallback_link'] = $node->toUrl()->toString();
      }

    }
  }
}
