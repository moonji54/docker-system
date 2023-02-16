<?php

namespace Drupal\nrgi_backend\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\project_frontend\PersonFeaturedWorkHelperService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Person Contributions Block.
 *
 * @Block(
 *   id = "person_contributions_block",
 *   admin_label = @Translation("Person Contributions block"),
 *   category = @Translation("Person"),
 * )
 */
class PersonContributionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The site settings loader service.
   *
   * @var \Drupal\project_frontend\PersonFeaturedWorkHelperService
   */
  protected $personFeaturedWork;

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\project_frontend\PersonFeaturedWorkHelperService $personFeaturedWork
   *   The site settings loader service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   The current route.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                              PersonFeaturedWorkHelperService $personFeaturedWork, CurrentRouteMatch $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->personFeaturedWork = $personFeaturedWork;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('project_frontend.person_featured_work'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current node.
    $node = $this->routeMatch->getParameter('node');

    // Set allowed types.
    $allowed_types = [
      'article',
      'publication',
      'event',
    ];

    // Set allowed fields.
    $fields = [
      'field_author',
      'field_speakers',
      ];

    // Set result limit.
    $limit = 1000;

    // Set view mode.
    $view_mode = 'card';

    // Call the personFeaturedWork service to get the node ids.
    $authored_work = $this->personFeaturedWork->getPersonFeaturedWorkNodes($node, $limit, $allowed_types, $view_mode, $fields);
    foreach ($authored_work as $work) {
      $build[] = [
        '#type' => 'markup',
        '#markup' => $work,
        ];
    }
    return $build;
  }

}
