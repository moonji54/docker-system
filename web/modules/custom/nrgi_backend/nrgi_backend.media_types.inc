<?php

/**
 * @file
 * Contains nrgi_backend.media_types.inc.
 */

use Drupal\media_library\Form\OEmbedForm;

/**
 * Implements hook_media_source_info_alter().
 */
function nrgi_backend_media_source_info_alter(array &$sources) {
  // Add remote audio as a type.
  $sources['remote_audio'] = [
    'id' => 'remote_audio',
    'label' => t('Remote audio'),
    'description' => t('Use audio from Audioboom.'),
    'allowed_field_types' => ['string'],
    'default_thumbnail_filename' => 'no-thumbnail.png',
    'providers' => ['Audioboom'],
    'deriver' => 'Drupal\media\Plugin\media\Source\OEmbedDeriver',
    'class' => 'Drupal\media\Plugin\media\Source\OEmbed',
    'provider' => 'media',
    'forms' => [
      // Make the new audio type use the media library add form.
      'media_library_add' => OEmbedForm::class,
    ],
  ];
}
