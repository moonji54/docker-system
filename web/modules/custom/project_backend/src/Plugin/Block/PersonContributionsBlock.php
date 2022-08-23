<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal\Core\Block\BlockBase;
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
class PersonContributionsBlock extends BlockBase implements Drupal\Core\Plugin\ContainerFactoryPluginInterface {

  /**
   * The site settings loader service.
   *
   * @var \Drupal\project_frontend\PersonFeaturedWorkHelperService
   */
  protected $personFeaturedWork;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
                              Drupal\project_frontend\PersonFeaturedWorkHelperService $personFeaturedWork) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->personFeaturedWork = $personFeaturedWork;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('project_frontend.person_featured_work')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    $t = $this->personFeaturedWork->getPersonFeaturedWorkNodes($node, 10, ['article', 'publication'], 'default');
    $s = 1;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['newsletter_signup_block'];
  }
}
