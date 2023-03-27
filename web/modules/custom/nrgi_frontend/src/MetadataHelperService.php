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
        'field_photo_caption',
      ],
      'downloads' => [
        'field_data_document',
        'field_supporting_document',
      ],
      'logo' => [
        'field_partner_logo',
      ],
      'sidebar_logo' => [
        'field_acknowledgement_logo',
      ]

    ],
    'event' => [
      'event_details' => [
        'field_contact',
        'field_course_information',
        'field_days_of_the_week',
        'field_time_commitment',
        'field_expertise_required',
        'field_address',
      ],
    ]
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
    $metadata = $this->preprocessLogos(
      $node,
      $this->metadataFieldNames['all']['logo'],
    );
    if (!empty($metadata)) {
      $metadata['label'] = t('Produced with financial support from');
      $variables['meta_data'][] = $metadata;
    }


    $this->preprocessEventDetails(
      $node,
      $this->metadataFieldNames['event']['event_details'],
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
  public function preprocessSidebarMetadata(
    NodeInterface $node,
    array &$variables
  ): void {
    $metadata = $this->preprocessLogos(
      $node,
      $this->metadataFieldNames['all']['sidebar_logo'],
    );

    if (!empty($metadata)) {
      $metadata['label'] = t('Produced in partnership with');
      $variables['sidebar_data'][] = $metadata;
    }

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
        $items = [];
        switch ($field->getFieldDefinition()->getType()) {
          case 'entity_reference':
            if ($field instanceof EntityReferenceFieldItemListInterface) {
              $entities = $node->get($metadata_field_name)->referencedEntities();
              $label = $node->get($metadata_field_name)
                ->getFieldDefinition()
                ->getLabel();
              foreach ($entities as $entity) {
                if ($entity instanceof EntityInterface) {
                  $url = $entity->toUrl()->toString();

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
            break;
          case 'string':
            $meta_items = [
              'label' => t('Top image'),
              'type' => 'text',
              'items' => ['title' => $node->get($metadata_field_name)->first()->value],
            ];
            break;
        }
      }
    }
    $variables['meta_data'][] = $metadata;
    $variables['meta_data'][] = $meta_items;
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

  /**
   * Preprocess logos.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param String[] $logo_field_names
   *   Array of logo field names.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function preprocessLogos(
    NodeInterface $node,
    array $logo_field_names,
  ): array {
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
      return [
        'type' => 'logo',
        'label' => t('Produced with financial support from'),
        'sections' => $metadata
      ];
    }
    return [];
  }

  /**
   * Preprocess Event meta.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param String[] $field_names
   *   Array of event field names.
   * @param array $variables
   *   The variables array.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function preprocessEventDetails(
    NodeInterface $node,
    array $field_names,
    array &$variables,
  ): void {
    foreach ($field_names as $field_name) {
      if ($node->hasField($field_name) && $field = $node->get($field_name)) {
        if (!$field instanceof FieldItemList | $field->isEmpty()) {
          continue;
        }
        switch ($field->getFieldDefinition()->getType()) {
          case 'string':
            $label = $node->get($field_name)
              ->getFieldDefinition()
              ->getLabel();
            $variables['meta_data'][] = [
              'label' => $label,
              'type' => 'text',
              'items' => ['title' => $node->get($field_name)->first()->value],
            ];
            break;
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
            if ($items) {
              $label = $node->get($field_name)
                ->getFieldDefinition()
                ->getLabel();
              $metadata = [
                'label' => $label,
                'items' => $items
              ];
              $variables['meta_data'][] = [$metadata];
            }
          break;
        }
      }
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
