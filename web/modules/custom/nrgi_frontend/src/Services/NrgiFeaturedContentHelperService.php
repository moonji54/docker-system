<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\nrgi_frontend\NrgiFeaturedCardsBase;

/**
 * Class NrgiFeaturedContentHelperService - service for featured content.
 */
class NrgiFeaturedContentHelperService extends NrgiFeaturedCardsBase {

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
  public function preprocessFeaturedContent(array &$variables): void {
    $this->setParagraph($variables['paragraph']);
    $this->setTaxonomyFields([
      'field_country',
      'field_region',
      'field_topic',
      'field_series',
    ]);

    $this->setAllowedTypes([
      'article',
      'career_opportunity',
      'event',
      'publication',
    ]);

    $upcoming_events_only = (bool) $this->paragraph->get('field_upcoming')
      ->getString();
    $date_filter = '';
    $date_fields = [];

    // If upcoming events only is flagged, we want to put a date
    // condition but for events only.
    if ($upcoming_events_only) {
      $date_filter = 'upcoming';
      $date_fields = [
        'event' => 'field_start_date',
      ];
    }

    $this->setDateFilter($date_filter);
    $this->setDateFields($date_fields);

    $content_nids_manually_excluded = $this->getManualExclusions();
    $content_nids_excluded = array_merge(
      $content_nids_manually_excluded,
      $this->getExclusions()
    );
    $content_nids_automated = $this->getAutomatedSelections(
      $content_nids_excluded
    );
    $content_nids_selected = $this->getNodeIds();

    if ($content_nids_automated) {
      $content_nids_automated = $content_nids_automated['node_ids'];
      $content_nids = array_merge(
        $content_nids_selected,
        $content_nids_automated
      );
    }
    else {
      $content_nids = $content_nids_selected;
    }

    if (!empty($content_nids)) {
      $this->setViewMode('featured_content');
      // Get node storage instance.
      $storage = $this->entityTypeManager->getStorage('node');
      $content_nodes = $storage->loadMultiple($content_nids);

      $variables['featured_content'] = [
        'items' => $this->renderFeaturedCards($content_nodes),
        'with_image' => $this->withImage,
        'view_mode' => $this->viewMode,
        'layout' => $this->paragraph->get($this->itemsPerRowField)->getString(),
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

    // Filter by content type(s).
    $query->condition('type', $this->types, 'IN');

    // Date filter.
    // Upcoming/past contents only.
    // Date filter.
    // Upcoming/past contents only.
    $this->sortOrder = 'DESC';
    if ($this->dateFilter === 'upcoming') {
      $this->sortOrder = 'ASC';
      $date_condition = '>';
    }
    elseif ($this->dateFilter === 'past') {
      $date_condition = '<';
    }
    if (isset($date_condition)) {
      $now = new DrupalDateTime('now');
      $date_format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
      $date_to_compare = $now->format($date_format);
      if (empty($this->dateFields)) {
        $query->condition(
          'unified_date',
          $date_to_compare,
          $date_condition
        );
      }
      else {
        // Array date fields.
        $types = [];
        foreach ($this->dateFields as $type => $field) {
          if ($this->types && in_array($type, $this->types)) {
            $or_group = $query->orConditionGroup()
              ->condition($field, $date_to_compare, $date_condition);
            $types[] = $type;
          }
        }

        if ($types) {
          $other_type_or_group = $query->orConditionGroup()
            ->condition('type', $types, 'NOT IN')
            ->condition($or_group);

          $query->condition($other_type_or_group);
        }
      }
    }

    // Taxonomy filters.
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
