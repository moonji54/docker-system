<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class FeaturedImageHelperService.
 *
 * @package Drupal\nrgi_frontend
 */
class NrgiResponsiveImageHelperService {

  /**
   * EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Drupal\Core\Render\RendererInterface definition.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * Constructs a new ResponsiveImageHelper object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer
  ) {

    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * Handle preprocess of responsive image for the given style.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The media item.
   * @param string $responsive_image_style
   *   The responsive image style.
   */
  public function preprocessResponsiveImage(MediaInterface $media, string $responsive_image_style, array &$variables): void {
    $image = $media->get('field_media_image')->entity;

    if (!empty($image)) {
      $settings = [
        'uri' => $image->getFileUri(),
      ];

      $image = \Drupal::service('image.factory')->get($settings['uri']);
      if ($image->isValid()) {
        if (!empty($image)) {
          $settings['responsive_image_style_id'] = $responsive_image_style;
          $settings['width'] = $image->getWidth();
          $settings['height'] = $image->getHeight();

          if (!empty($settings['uri'])) {
            $variables['responsive_image'] = [
              '#theme' => 'responsive_image',
              '#width' => $settings['width'],
              '#height' => $settings['height'],
              '#responsive_image_style_id' => $settings['responsive_image_style_id'],
              '#uri' => $settings['uri'],
              '#attributes' => ['alt' => $media->thumbnail->alt ?: $media->getName()],
            ];
          }
        }
      }
    }
  }

}