<?php

/**
 * @file
 * Logic for backend paragraph.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_field_widget_WIDGET_TYPE_form_alter().
 */
function nrgi_backend_field_widget_single_element_paragraphs_form_alter(&$element, FormStateInterface $form_state, $context) {
  // Get the item delta for multiple CTA elements on the same editor form.
  $delta = $context['delta'];

  // Conditional fields for call_to_action paragraph.
  if ($element['#paragraph_type'] === 'call_to_action') {
    // field_link only visible if field_type value is 'link'.
    $element['subform']['field_link']['#states'] = [
      'visible' => [
        [
          '[name="field_page_builder[' . $delta . '][subform][field_type]"]' => ['value' => 'link'],
        ],
      ],
    ];

    // field_link only visible if field_type value is 'download'.
    $element['subform']['field_file']['#states'] = [
      'visible' => [
        [
          '[name="field_page_builder[' . $delta . '][subform][field_type]"]' => ['value' => 'download'],
        ],
      ],
    ];
  }
}
