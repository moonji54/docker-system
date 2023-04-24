<?php

/**
 * @file
 * Node pdf hooks.
 */

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

/**
 * Implements hook_soapbox_pdf_request_alter().
 */
function nrgi_frontend_soapbox_pdf_request_alter($url, array &$options, $type) {
  $options['json']['options']['format'] = 'A4';
  $options['json']['options']['scale'] = 1.224;
  $options['json']['options']['displayHeaderFooter'] = FALSE;
  $options['json']['options']['printBackground'] = TRUE;
  $options['json']['userAgent'] = "ngrok nrgi user-agent";
}

/**
 * Implements template_preprocess_field().
 */
function nrgi_frontend_preprocess_field(&$variables) {
  $pdf_components = [
    'embed',
    'full_width_media',
    'learning_block',
    'quote',
    'wysiwyg',
  ];

  // If view mode is pdf show only available components.
  if (
    $variables['field_name'] == 'field_page_builder'
    && $variables['element']['#view_mode'] === 'pdf'
  ) {
    foreach ($variables['items'] as $id => $item) {
      if (!in_array($variables['items'][$id]['content']['#paragraph']->bundle(), $pdf_components)) {
        unset($variables['items'][$id]);
      }
    }
  }
}

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
