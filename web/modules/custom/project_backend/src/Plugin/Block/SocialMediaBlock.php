<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a Social Media Block.
 *
 * @Block(
 *   id = "social_media_block",
 *   admin_label = @Translation("Social Media block"),
 *   category = @Translation("Social Media"),
 * )
 */
class SocialMediaBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the site settings
    $social_media_links = Drupal::service('site_settings.loader')->loadByFieldset('social_media_links')['social_media_links'];
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
