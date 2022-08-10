<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a Social Media Share Block.
 *
 * @Block(
 *   id = "social_media_share_block",
 *   admin_label = @Translation("Social Media Share block"),
 *   category = @Translation("Social Media Share"),
 * )
 */
class SocialMediaShareBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the site settings
    $t = 1;
    return $build = [
      '#theme' => 'block--share-block',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['social_media_block'];
  }
}
