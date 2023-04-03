<?php

/**
 * @file
 * Paragraphs form modifications.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_field_widget_WIDGET_TYPE_form_alter().
 */
function nrgi_backend_field_widget_single_element_paragraphs_form_alter(
  &$element,
  FormStateInterface $form_state,
  $context
) {
  // Get the item delta for multiple CTA elements on the same editor form.
  $delta = $context['delta'];

  $file_or_link_button_paragraphs = ['call_to_action', 'learning_block'];

  // Conditional fields for call_to_action paragraph.
  if (in_array($element['#paragraph_type'], $file_or_link_button_paragraphs)) {
    // field_link visible if field_type value is 'link'.
    $element['subform']['field_link']['#states'] = [
      'visible' => [
        [
          '[name="field_page_builder[' . $delta . '][subform][field_type]"]' =>
            [
              'value' => 'link',
            ],
        ],
      ],
    ];

    // field_file visible if field_type value is 'download'.
    $element['subform']['field_file']['#states'] = [
      'visible' => [
        [
          '[name="field_page_builder[' . $delta . '][subform][field_type]"]' =>
            [
              'value' => 'download',
            ],
        ],
      ],
    ];

    // field_download_button_label visible if field_type value is 'download'.
    $element['subform']['field_download_button_label']['#states'] = [
      'visible' => [
        [
          '[name="field_page_builder[' . $delta . '][subform][field_type]"]' =>
            [
              'value' => 'download',
            ],
        ],
      ],
    ];
  }

  $title_size_option_paragraphs = ['featured_pages'];

  // Conditional title size field.
  if (in_array($element['#paragraph_type'], $title_size_option_paragraphs)) {
    // field_title_size visible if field_title has a value.
    $element['subform']['field_title_size']['#states'] = [
      'visible' => [
        [
          ':input[name="field_page_builder[' . $delta . '][subform][field_title][0][value]"]' =>
            [
              'filled' => TRUE,
            ],
        ],
      ],
    ];

  }

}
