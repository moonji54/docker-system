<?php

namespace Drupal\nrgi_frontend;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Class PersonFeaturedWorkHelperService.
 *
 * Helper class to provide a person's featured work nodes.
 * Featured work is the latest content where a person has
 * been tagged as an author.
 *
 * @package Drupal\nrgi_frontend
 */
class PersonFeaturedWorkHelperService {

  /**
   * The entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer interface.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * PersonFeaturedWorkHelperService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * Get person's featured work.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The person node to get person featured work content for.
   * @param int $limit
   *   Maximum number of person featured work content to pull.
   * @param string[] $allowed_types
   *   Allowed node types for getting person featured work content from.
   * @param string $view_mode
   *   View mode used for rendering person featured work content.
   * @param array $fields
   *   Allowed fields for getting the person featured work content from.
   *
   * @return array
   *   Array of person's work nodes rendered by given view mode/
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function getPersonFeaturedWorkNodes(NodeInterface $node, $limit, array $allowed_types, $view_mode, $fields) {
    $featured_work_nodes = [];

    if ($node->bundle() == 'person' && $limit > 0) {
      $query = $this->entityTypeManager->getStorage('node')
        ->getQuery();
      $query->condition('status', 1)
        ->condition('type', $allowed_types, 'IN');

      // Create or condition group to check if the node id == one of the supplied fields value.
      $orGroup = $query->orConditionGroup();
      foreach ($fields as $field) {
        $orGroup->condition($field, $node->id());
      }
      $query->condition($orGroup);

      $nids = $query->range(0, $limit)
        ->sort('created', 'DESC')
        ->execute();

      if ($nids) {
        $view_builder = $this->entityTypeManager->getViewBuilder('node');
        foreach ($nids as $nid) {
          $featured_node = Node::load($nid);
          $build = $view_builder->view($featured_node, $view_mode);
          $featured_work_nodes[] = $this->renderer->render($build);
        }
      }

    }
    return $featured_work_nodes;
  }

}
