<?php

namespace Drupal\nrgi_backend\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\site_settings\SiteSettingsLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Newsletter Signup Block.
 *
 * @Block(
 *   id = "newsletter_signup_block",
 *   admin_label = @Translation("Newsletter Signup  block"),
 *   category = @Translation("Newsletter Signup  Media"),
 * )
 */
class NewsletterSignupBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\site_settings\SiteSettingsLoader
   */
  protected $siteSettingsLoader;

  /**
   * @param \Drupal\site_settings\SiteSettingsLoader $siteSettingsLoader
   *   The site settings loader service.
   */
  public function __construct(
    array $configuration, $plugin_id, $plugin_definition,
    SiteSettingsLoader $siteSettingsLoader) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
    // Load the site settings
    $newsletter = $this->siteSettingsLoader->loadByFieldset('newsletter')['newsletter'];
    if ($newsletter == '') {
      return [];
    }

    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['newsletter_signup']],
    ];

    $build['title'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => ['class' => ['signup_title']],
      '#value' => t($newsletter['field_title']),
    ];

    $build['description'] = [
      '#type' => 'processed_text',
      '#text' => t($newsletter['field_description']['value']),
      '#format' => 'text_and_links',
      '#attributes' => ['class' => ['signup_description']],
    ];

    $build['button'] = [
      '#type' => 'link',
      '#title' => t($newsletter['field_subscribe_button']['title']),
      '#url' => Url::fromUri($newsletter["field_subscribe_button"]["uri"]),
      '#attributes' => ['class' => ['signup_button']],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['newsletter_signup_block'];
  }
}
