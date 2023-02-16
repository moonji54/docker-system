<?php

namespace Drupal\nrgi_backend\Plugin\Block;

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
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\site_settings\SiteSettingsLoader $siteSettingsLoader
   *   The site settings loader service.
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
