<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Related Content Block.
 *
 * @Block(
 *   id = "related_content_block",
 *   admin_label = @Translation("Related Content block"),
 *   category = @Translation("Content"),
 * )
 */
class RelatedContentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   The current route.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(
    array $configuration, $plugin_id, $plugin_definition,
    CurrentRouteMatch $routeMatch, EntityTypeManager $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current node and topics.
    $node = $this->routeMatch->getParameter('node');

    // Return empty array if the node doesn't have the topics field.
    if (!$node->hasField('field_topic')) {
      return [];
    }

    // Return empty array if the topic field is empty.
    if ($node->field_topic->isEmpty()) {
      return [];
    }

    $topics = [];
    foreach ($node->field_topic->getValue() as $topic) {
      $topics[] = $topic['target_id'];
    }

    // Fetch the node ids with one or multiple same topics as the current node.
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $node_ids = $query->condition('status', '1')
      ->condition('field_topic', $topics, 'IN')
      ->sort('created', 'desc')
      ->execute();

    // Remove the current node from the node ids.
    if (($key = array_search($node->id(), $node_ids)) !== FALSE) {
      unset($node_ids[$key]);
    }

    // Load the nodes and return in specific view mode.
    $nodes = Node::loadMultiple($node_ids);
    return $list = [
      'nodes' => $this->entityTypeManager->getViewBuilder('node')
        ->viewMultiple($nodes, 'list_item')
    ];
  }

}
