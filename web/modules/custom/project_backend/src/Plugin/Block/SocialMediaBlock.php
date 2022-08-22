<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\site_settings\SiteSettingsLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Social Media Block.
 *
 * @Block(
 *   id = "social_media_block",
 *   admin_label = @Translation("Social Media block"),
 *   category = @Translation("Social Media"),
 * )
 */
class SocialMediaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The site settings loader service.
   *
   * @var \Drupal\site_settings\SiteSettingsLoader
   */
  protected $siteSettingsLoader;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
    SiteSettingsLoader $siteSettingsLoader) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->siteSettingsLoader = $siteSettingsLoader;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('site_settings.loader')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the site settings
    $social_media_links = $this->siteSettingsLoader->loadByFieldset('social_media_links')['social_media_links'];
    if ($social_media_links == []) {
      return [];
    }
    return $build = [
      '#theme' => 'social_media_links',
      '#social_network' => $social_media_links,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['social_media_block'];
  }
}
