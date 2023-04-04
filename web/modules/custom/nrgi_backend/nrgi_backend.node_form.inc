<?php

/**
 * @file
 * Node form modifications.
 */

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

/**
 * Implements hook_node_form_alter().
 */
function nrgi_backend_form_node_form_alter(
  &$form,
  &$form_state,
  $form_id
) {
  // Get the node.
  /** @var \Drupal\node\NodeInterface $node */
  $node = $form_state->getFormObject()->getEntity();

  $resources_content_types = ['article', 'publication'];

  switch ($node->bundle()) {
    case 'article':
    case 'publication':
      /* 'Resources' content types (Articles, Publications) conditional fields. */

      // If resource type is 'Course materials' make Publication date optional.
      $course_material_input = _nrgi_backend_get_term_reference_id(
        'Course Materials',
        'resource_type'
      );

      $form['field_publication_date']['widget'][0]['value']['#states'] = [
        'optional' => [
          ':input[name="field_resource_type"]' => ['value' => $course_material_input],
        ],
      ];

      // Force the datetime field label to show when field is required.
      $form['field_publication_date']['#states'] = [
        'optional' => [
          ':input[name="field_resource_type"]' => ['value' => $course_material_input],
        ],
      ];

      /*
       * If resource type is 'External news' or 'Tool':
       *    - make field_disable visible.
       *    - make field_link required if field_disable is checked.
       */
      $external_news_input = _nrgi_backend_get_term_reference_id(
        'External news',
        'resource_type'
      );
      $tool_input = _nrgi_backend_get_term_reference_id(
        'Tool',
        'resource_type'
      );

      $external_item_condition = [
        [
          ':input[name="field_resource_type"]' => [
            ['value' => $external_news_input],
            'or',
            ['value' => $tool_input],
          ],
        ],
      ];

      $form['field_disable']['#states'] = [
        'visible' => $external_item_condition,
      ];

      /* If field_disable is checked. */

      // Make URL required.
      // Also adding initial Drupal's default field required conditional
      // as we are overriding the existing states attribute values
      // array (need to be required if link text is provided).
      $form['field_link']['widget'][0]['uri']['#states'] = [
        'required' => [
          [':input[name="field_link[0][title]"]' => ['filled' => TRUE]],
          'or',
          [':input[name="field_disable[value]"]' => ['checked' => TRUE]],
        ],
      ];

      // Hide page builder field.
      $form['field_page_builder']['#states'] = [
        'invisible' => [
          ':input[name="field_disable[value]"]' => ['checked' => TRUE],
        ],
      ];

      // Hide Body field group.
      $form['group_body']['#states'] = [
        'invisible' => [
          ':input[name="field_disable[value]"]' => ['checked' => TRUE],
        ],
      ];

      // Hide field_upload (News upload).
      $form['field_upload']['#states'] = [
        'invisible' => [
          ':input[name="field_disable[value]"]' => ['checked' => TRUE],
        ],
      ];

      // Hide field_data_document (Data document).
      $form['field_data_document']['#states'] = [
        'invisible' => [
          ':input[name="field_disable[value]"]' => ['checked' => TRUE],
        ],
      ];

      // Hide field_supporting_document (Supporting documents).
      $form['field_supporting_document']['#states'] = [
        'invisible' => [
          ':input[name="field_disable[value]"]' => ['checked' => TRUE],
        ],
      ];

      // Hide field_acknowledgement_logo (Acknowledgement donors logos).
      $form['field_acknowledgement_logo']['#states'] = [
        'invisible' => [
          ':input[name="field_disable[value]"]' => ['checked' => TRUE],
        ],
      ];

      // Hide field_partner_logo (Partners logos).
      $form['field_partner_logo']['#states'] = [
        'invisible' => [
          ':input[name="field_disable[value]"]' => ['checked' => TRUE],
        ],
      ];
      break;

    case 'person':
      $board_member_input = _nrgi_backend_get_term_reference_id(
        'Board member',
        'person_type'
      );
      /* Person conditional fields. */

      $board_member_condition = [
        [
          ':input[name="field_role_type"]' => [
            ['value' => $board_member_input],
          ],
        ],
      ];

      // Show Board member type field when role type is 'Board member'.
      $form['field_board_member_type']['#states'] = [
        'visible' => $board_member_condition,
      ];

      break;
  }
}

/**
 * Helper function returning the Term id for a given Term name.
 *
 * @param string $term_name
 *   The term's name.
 * @param string $vocabulary_id
 *   The taxonomy vocabulary id (machine name).
 *
 * @return string|null
 *   The term id reference string, null if term not found.
 */
function _nrgi_backend_get_term_reference_id(
  string $term_name,
  string $vocabulary_id
): ?string {
  /** @var \Drupal\taxonomy\Entity\Term $term */
  try {
    $term = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $term_name, 'vid' => $vocabulary_id]);
  }
  catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
    return NULL;
  }

  if ($term) {
    return (string) reset($term)->id();
  }
  return NULL;
}
