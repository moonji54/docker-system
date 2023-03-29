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
function nrgi_backend_form_node_form_alter(&$form, &$form_state, $form_id) {
  // Get the node.
  /** @var \Drupal\node\NodeInterface $node */
  $node = $form_state->getFormObject()->getEntity();

  // Article node form conditional fields.
  if ($node->bundle() === 'article') {
    // If resource type is 'Course materials' make Publication date optional.
    $course_material_input = _nrgi_backend_get_term_reference_id(
      'Course Materials', 'resource_type'
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
function _nrgi_backend_get_term_reference_id(string $term_name, string $vocabulary_id): ?string {
  /** @var \Drupal\taxonomy\Entity\Term $term */
  try {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
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
