<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\nrgi_frontend\NrgiFeaturedCardsBase;

/**
 * Class NrgiFeaturedPeopleHelperService - service for featured people.
 */
class NrgiFeaturedPeopleHelperService extends NrgiFeaturedCardsBase {

  /**
   * Handle preprocess Paragraph for featured cards.
   *
   * @param array $variables
   *   Variables from hook_preprocess_paragraph().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function preprocessFeaturedPeople(array &$variables): void {
    $this->setParagraph($variables['paragraph']);
    $this->setTaxonomyFields(['field_role_type']);

    $people_nids_manually_excluded = $this->getManualExclusions();
    $people_nids_excluded = array_merge(
      $people_nids_manually_excluded,
      $this->getExclusions()
    );
    $people_nids_automated = $this->getAutomatedSelections(
      $people_nids_excluded
    );
    $people_nids_selected = $this->getNodeIds();

    if ($people_nids_automated) {
      $people_nids_automated = $people_nids_automated['node_ids'];
      $people_nids = array_merge($people_nids_selected, $people_nids_automated);
    }
    else {
      $people_nids = $people_nids_selected;
    }

    if (!empty($people_nids)) {
      $this->setViewMode('featured_people');
      // Get node storage instance.
      $storage = $this->entityTypeManager->getStorage('node');
      $people_nodes = $storage->loadMultiple($people_nids);

      $variables['featured_people'] = [
        'items' => $this->renderFeaturedCards($people_nodes),
        'with_image' => $this->withImage,
        'view_mode' => $this->viewMode,
        'layout' => '3_per_row',
        'cta' => $this->paragraph->hasField($this->linkField)
          ? $this->paragraphButtonLinkHelperService->getLinkFieldValues(
            $this->paragraph,
            $this->linkField)
          : '',
      ];
    }
  }

  /**
   * Get automatic selections based on paragraph settings.
   *
   * @param array $exclusions
   *   The NIDs to be excluded.
   *
   * @return array|bool
   *   Array of node ids, false if nothing found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAutomatedSelections(array $exclusions): bool | array {
    // Use drupal_static to prevent duplicated cards.
    $nodes = &drupal_static(__FUNCTION__);
    $previous_exclusions = [];
    if ($nodes) {
      $previous_exclusions = array_merge(
        $nodes['node_ids'],
        $nodes['previous_exclusions']
      );
      $exclusions = array_merge($exclusions, $previous_exclusions);
    }
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery();

    // Add current node id to exclusions.
    $parent_entity = $this->paragraph->getParentEntity();
    if ($parent_entity) {
      $exclusions[] = $parent_entity->id();
    }

    // Maybe exclude node ids we already have rendered on the page.
    if ($exclusions) {
      $query->condition('nid', $exclusions, 'NOT IN');
    }

    // Filter by person type.
    $query->condition('type', ['person'], 'IN');

    // Date filter.
    // Upcoming/past contents only.
    $this->sortOrder = 'DESC';

    foreach ($this->taxonomyFields as $taxonomy_field) {
      $term_ids = [];

      // Filter by tags.
      if ($this->paragraph->hasField($taxonomy_field)) {
        foreach ($this->paragraph->get($taxonomy_field) as $term) {
          if ($term->entity) {
            $term_ids[] = $term->entity->id();
          }
        }
      }

      if ($term_ids) {
        $query->condition($taxonomy_field, $term_ids, 'IN');
      }
    }

    // Only published nodes.
    $query->condition('status', 1);
    // Sort by desc for most recency.
    $query->sort('unified_date', 'DESC');

    // Limit.
    $query->range(0, $this->paragraph->get($this->quantityField)->value);
    $node_ids = $query->execute();

    // If we have results, load the objects in a single query.
    if ($node_ids) {
      // Return node ids and previous exclusions.
      return [
        'node_ids' => $node_ids,
        'previous_exclusions' => $previous_exclusions,
      ];
    }

    return FALSE;
  }

}
