<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\file\FileInterface;
use Drupal\link\LinkItemInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class Metadata Helper service.
 *
 * Service providing preprocessing methods for nodes metadata.
 *
 * @package Drupal\nrgi_frontend
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
      ],

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
    ],
  ];

  /**
   * The path matcher service. Provides a path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected PathMatcherInterface $pathMatcher;

  /**
   * The date formatter service. Provides service to handle date formatting.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected DateFormatterInterface $dateFormatter;

  /**
   * Constructs a new MetadataHelperService.
   *
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher interface.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter interface.
   */
  public function __construct(
    PathMatcherInterface $path_matcher,
    DateFormatterInterface $date_formatter
  ) {
    $this->pathMatcher = $path_matcher;
    $this->dateFormatter = $date_formatter;

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
   * Preprocess cards metadata.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param array &$variables
   *   The variables array.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function preprocessCardMetadata(
    NodeInterface $node,
    array &$variables
  ): void {

    // Content type.
    $variables['content_type'] = $node->bundle();

    // Slug - on homepage only.
    if ($node->hasField('field_slug')
        && $slug_field = $node->get('field_slug')) {
      $slug = $slug_field->value;

      if ($this->pathMatcher->isFrontPage()) {
        $variables['slug'] = $slug;
      }
    }

    // Date.
    if ($date = $node->get('unified_date')) {
      $card_date = $this->dateFormatter->format($date->value, 'card_date');
      $variables['date'] = $card_date;
    }

    // Available translations.
    $available_translations_string = '';
    $languages = $node->getTranslationLanguages();
    $available_langcodes = count(array_keys($languages)) > 1 ? array_keys($languages) : [];

    if ($available_langcodes) {
      $available_translations_string .= t('Also in');
      $i = 0;
      foreach ($available_langcodes as $available_langcode) {
        if ($i > 0) {
          $available_translations_string .= ',';
        }
        if ($available_langcode != $node->language()->getId()) {
          $available_translations_string .= ' ' . $available_langcode;
          $i++;
        }

      }
      $variables['translations'] = $available_translations_string;
    }

    // First Topic.
    $variables['first_topic'] = $this->getFirstTermLabel($node, 'field_topic');

    // Content type specific card meta.
    switch ($node->bundle()) {
      case 'event':
        $this->preprocessEventCardMetadata($node, $variables);
        break;

      case 'article':
      case 'publication':
        $this->preprocessResourceCardMetadata($node, $variables);
        break;

    }

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
              $entities = $node->get($metadata_field_name)
                ->referencedEntities();
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
              'items' => [
                'title' => $node->get($metadata_field_name)
                  ->first()->value,
              ],
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
        'items' => $items,
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
                  $view_builder = \Drupal::entityTypeManager()
                    ->getViewBuilder('media');
                  $items[] = [
                    'view' => $view_builder->view($paragraph->get('field_image')->entity, 'logo'),
                    'link' => $paragraph->get('field_link')
                      ->first()
                      ->getUrl()
                      ->toString(),
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
        'sections' => $metadata,
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
                'items' => $items,
              ];
              $variables['meta_data'][] = [$metadata];
            }
            break;
        }
      }
    }
  }

  /**
   * Preprocess Event card meta.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param array $variables
   *   The variables array.
   */
  protected function preprocessEventCardMetadata(
    NodeInterface $node,
    array &$variables,
  ): void {

    if ($event_type = $this->getFirstTermLabel($node, 'field_event_type')) {
      $variables['subtype'] = $event_type;
    }

    // Dates.
    $date = '';
    if ($node->hasField('field_start_date')
        && $start_date_field = $node->get('field_start_date')) {
      $start_date = new DrupalDateTime($start_date_field->value);
      $start_day = $start_date->format('d');
      $start_month = $start_date->format('F');
      $start_year = $start_date->format(('Y'));

      $date .= $start_day . ' ' . $start_month;

      if ($node->hasField('field_end_date')
          && $end_date_field = $node->get('field_end_date')) {
        $end_date = new DrupalDateTime($end_date_field->value);
        $end_day = $end_date->format('d');
        $end_month = $end_date->format('F');
        $end_year = $end_date->format(('Y'));

        if ($end_year > $start_year) {
          $date .= ' ' . $start_year . '-' . $end_day . ' ' . $end_month . ' ' . $end_year;
        }
        else {
          $date .= '-' . $end_day . ' ' . $end_month . ' ' . $end_year;
        }

        $variables['date'] = $date;
      }
    }

    // Format.
    if ($node->hasField('field_format')
        && $format_field = $node->get('field_format')) {
      $format = $format_field->value;

      $variables['format'] = $format;
    }

    // Recording available.
    if ($node->hasField('field_event_recording')
        && $recording_field = $node->get('field_event_recording')) {
      $has_recording = $recording_field->value;
      $now = new DrupalDateTime('now');
      $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      if ($has_recording && $end_date < $now) {
        $variables['recording'] = TRUE;
      }
    }

  }

  /**
   * Preprocess Resource card meta.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param array $variables
   *   The variables array.
   */
  protected function preprocessResourceCardMetadata(
    NodeInterface $node,
    array &$variables,
  ): void {

    // Resource type.
    if ($resource_type = $this->getFirstTermLabel(
      $node,
      'field_resource_type')
    ) {
      $variables['subtype'] = $resource_type;
    }

    // External link - node page disabled - card title to link to external item.
    if ($node->hasField('field_link')
        && $external_link_field = $node->get('field_link')) {
      if ($external_link = $external_link_field->first()) {
        if ($external_link instanceof LinkItemInterface) {
          $external_link_uri = $external_link->get('uri')->getValue();
          $external_link_title = $external_link->get('title')->getValue();

          if ($external_link_uri) {
            if ($node->hasField('field_disable')
                && $disable_field = $node->get('field_disable')) {
              if ($disable_field->value) {
                $variables['external_link'] = [
                  'uri' => $external_link_uri,
                  'title' => $external_link_title,
                ];
              }
            }
          }
        }
      }
    }

  }

  /**
   * Get first selected term label.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $taxonomy_field_name
   *   The taxonomy field name.
   *
   * @return string
   *   The first term's selected label, empty string if not found.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getFirstTermLabel(
    NodeInterface $node,
    string $taxonomy_field_name
  ) {
    if ($node->hasField($taxonomy_field_name)
        && $taxonomy_field = $node->get($taxonomy_field_name)) {
      if ($entity_ref = $taxonomy_field->first()
        ->get('entity')) {
        if ($entity_ref instanceof EntityReference) {
          if ($entity_ref->getTarget()) {
            $entity = $entity_ref->getTarget()
              ->getValue();
            if ($entity instanceof TermInterface) {
              return $entity->getName();
            }
            elseif ($entity instanceof NodeInterface) {
              return $entity->label();
            }
          }
        }
      }
    }
    return '';
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
