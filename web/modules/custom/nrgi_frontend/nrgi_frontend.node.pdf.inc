<?php

/**
 * @file
 * Node pdf hooks.
 */

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
 * Implements hook_library_info_alter().
 */
function nrgi_frontend_library_info_alter(&$libraries, $extension) {
  if ($extension === 'soapbox_pdf') {
    $libraries['pagedjs']['header'] = TRUE;
    $libraries['pagedjs']['js'] = [
      'https://unpkg.com/pagedjs/dist/paged.polyfill.js' => [
        'external' => TRUE,
      ],
    ];
  }
}
