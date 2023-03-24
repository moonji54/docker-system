<?php

namespace Drupal\nrgi_frontend;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\file\FileInterface;
use Drupal\link\Plugin\Field\FieldType\LinkItem;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class Metadata Helper service.
 *
 * Service providing preprocessing methods for nodes metadata,
 *
 * @package Drupal\ifg_frontend
 */
class MetadataHelperService {

  /**
   * Array of field names per content type.
   *
   * @var array
   */
  protected array $metadataFieldNames = [
    'all' => [
      'taxonomies' => [
        'field_country',
        'field_region',
        'field_topic',
        'field_keywords',
        'field_career_opportunity_type',
        'field_city',
        'field_event_type',
        'field_role_type',
      ],
      'downloads' => [
        'field_data_document',
        'field_supporting_document',
      ],
      'logo' => [
        'field_partner_logo',
      ],
    ],
  ];

  /**
   * Preprocess metadata.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param array &$variables
   *   The variables array.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function preprocessMetadata(
    NodeInterface $node,
    array &$variables
  ): void {
    $this->preprocessLogos(
      $node,
      $this->metadataFieldNames['all']['logo'],
      $variables
    );
    $this->preprocessDownloads(
      $node,
      $this->metadataFieldNames['all']['downloads'],
      $variables
    );
    $this->preprocessGeneralMetadata(
      $node,
      $this->metadataFieldNames['all']['taxonomies'],
      $variables
    );
  }

  /**
   * Preprocess general metadata.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param String[] $metadata_field_names
   *   Array of metadata field names.
   * @param array $variables
   *   The variables array.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function preprocessGeneralMetadata(
    NodeInterface $node,
    array $metadata_field_names,
    array &$variables
  ): void {
    $metadata = [];
    foreach ($metadata_field_names as $metadata_field_name) {
      if ($node->hasField($metadata_field_name) && $field = $node->get($metadata_field_name)) {
        if ($field instanceof EntityReferenceFieldItemListInterface) {
          $entities = $node->get($metadata_field_name)->referencedEntities();
          $label = $node->get($metadata_field_name)
            ->getFieldDefinition()
            ->getLabel();
          $items = [];
          foreach ($entities as $entity) {

            if ($entity instanceof EntityInterface) {
              $bundle = $entity->bundle();

              if ($bundle == 'node' || $bundle == 'topic') {
                $url = $entity->toUrl()->toString();
              }

              if ($bundle == 'tracker' &&
                  ($entity->hasField('field_listing') && $tracker_listing = $entity->get('field_listing')
                    ->first())) {
                if ($tracker_listing instanceof EntityReferenceItem) {
                  $url = $tracker_listing->get('entity')
                    ->getTarget()
                    ->getValue()
                    ->toUrl()
                    ->toString();
                }
              }

              else {
                $url = match ($bundle) {
                  'keyword' => "/search/?" . $bundle . '=' . $entity->label() . '%20(' . $entity->id() . ')',
                  default => "/search/?" . $bundle . '[' . $entity->id() . ']=' . $entity->id(),
                };
              }
              $items[] = [
                'title' => $entity->label(),
                'url' => $url,
              ];
            }
          }
          if ($items) {
            $metadata[] = [
              'label' => $label,
              'items' => $items,
            ];
          }
        }
      }
    }
    $variables['meta_data'][] = $metadata;
  }

  /**
   * Preprocess downloads.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param String[] $download_field_names
   *   Array of download field names.
   * @param array $variables
   *   The variables array.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function preprocessDownloads(
    NodeInterface $node,
    array $download_field_names,
    array &$variables,
  ): void {
    $metadata = [];
    $items = [];
    foreach ($download_field_names as $download_field_name) {
      if ($node->hasField($download_field_name) && $field = $node->get($download_field_name)) {
        if (!$field instanceof FieldItemList | $field->isEmpty()) {
          continue;
        }
        switch ($field->getFieldDefinition()->getType()) {
          case 'entity_reference':
          case 'entity_reference_revisions':
            $entity_fields = $field->referencedEntities();
            foreach ($entity_fields as $paragraph) {
              if ($paragraph instanceof ParagraphInterface) {
                $media = $paragraph->get('field_file')->entity;
                if ($media instanceof MediaInterface) {
                  $items[] = $this->getFileFromMediaDocument($media);
                }
              }
            }
            break;
        }
      }
    }
    if ($items) {
      $metadata = [
        'label' => 'Additional downloads',
        'items' => $items
      ];
      $variables['meta_data'][] = [$metadata];
    }
  }

  protected function preprocessLogos(
    NodeInterface $node,
    array $logo_field_names,
    array &$variables,
  ): void {
    $metadata = [];
    foreach ($logo_field_names as $logo_field_name) {
      if ($node->hasField($logo_field_name) && $field = $node->get($logo_field_name)) {
        if (!$field instanceof FieldItemList | $field->isEmpty()) {
          continue;
        }
        $items = [];
        switch ($field->getFieldDefinition()->getType()) {
          case 'entity_reference':
          case 'entity_reference_revisions':
            $entity_fields = $field->referencedEntities();
            foreach ($entity_fields as $paragraph) {
              if ($paragraph instanceof ParagraphInterface) {
                $media = $paragraph->get('field_image')->entity;
                if ($media instanceof MediaInterface) {
                  $view_builder = \Drupal::entityTypeManager()->getViewBuilder('media');
                  $items[] = [
                    'view' => $view_builder->view($paragraph->get('field_image')->entity, 'logo'),
                    'link' => $paragraph->get('field_link')->first()->getUrl()->toString(),
                  ];
                }
              }
            }
            if ($items) {
              $metadata = $items;
            }
            break;
        }
      }
    }
    if ($metadata) {
      $variables['meta_data'][] = [
        'type' => 'logo',
        'label' => t('Produced with financial support from'),
        'sections' => $metadata
      ];
    }
  }

  /**
   * Gets a file array from Media document.
   *
   * @param \Drupal\media\MediaInterface $media
   *   Media item to get file from.
   *
   * @return array|null
   *   File array.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getFileFromMediaDocument(MediaInterface $media): ?array {

    /** @var \Drupal\file\Entity\File $file */
    $file = $media->hasField('field_media_document')
            && !($media->get('field_media_document')->isEmpty())
      ? $media->get('field_media_document')->entity : NULL;

    if ($file instanceof FileInterface) {
      $file_size = format_size($file->getSize());
      $file_type = $file->getMimeType();
      $file_type = strtoupper(explode('/', $file_type,)[1]);
      $document_label =
        $media->hasField('field_media_label')
        && !$media->get('field_media_label')->isEmpty()
        && ($media->get('field_media_label')
            && $file_label = $media->get('field_media_label')->first()->value)
          ? $file_label
          : $file->getFilename();
      return [
        'type' => 'file',
        'file_type' => $file_type,
        'file_size' => $file_size,
        'title' => $document_label,
        'url' => $file->createFileUrl(),
      ];
    }
    return NULL;
  }

}
