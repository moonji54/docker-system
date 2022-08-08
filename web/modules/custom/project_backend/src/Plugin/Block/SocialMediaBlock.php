<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

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
    $config = Drupal::Config('project_backend.settings');
    foreach ($config->get('project_backend.social') as $social_network => $url) {
      if ($url != '') {
        $social_menu[$social_network] = [
          'title' => $social_network,
          'url' => Url::fromUri($url, ['attributes' => ['class' => [$social_network]]]),
        ];
      }
    }
    $build = [
      '#theme' => 'social_media_links',
      '#attributes' => ['class' => ['links']],
      '#social_network' => $social_menu,
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['social_media_block'];
  }
}
