<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a Newsletter Signup Block.
 *
 * @Block(
 *   id = "newsletter_signup_block",
 *   admin_label = @Translation("Newsletter Signup  block"),
 *   category = @Translation("Newsletter Signup  Media"),
 * )
 */
class NewsletterSignupBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $newsletter = Drupal::service('site_settings.loader')->loadByFieldset('newsletter')['newsletter'];
    if ($newsletter == []) {
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
