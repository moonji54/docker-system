<?php

namespace Drupal\nrgi_frontend\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SearchBlock.
 *
 * @Block(
 *   id = "search_block",
 *   admin_label = @Translation("NRGI search form"),
 *   category = @Translation("NRGI custom"),
 * )
 */
class NrgiSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Form Builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $form = $this->formBuilder->getForm('Drupal\nrgi_frontend\Form\NrgiSearchForm');
    return [
      '#theme' => 'nrgi_search_form',
      '#form' => $form,
    ];
  }

}
