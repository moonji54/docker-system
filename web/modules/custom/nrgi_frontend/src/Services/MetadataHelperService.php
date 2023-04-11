<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\entity_reference_revisions\Plugin\DataType\EntityReferenceRevisions;
use Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem;
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
   * Associative array of subtype field names by content type.
   *
   * @var array|string[]
   */
  protected array $nodeSubtypeFields = [
    'article' => 'field_resource_type',
    'publication' => 'field_resource_type',
    'event' => 'field_event_type',
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
   * The NRGI responsive image helper service.
   *
   * @var \Drupal\nrgi_frontend\Services\NrgiResponsiveImageHelperService
   */
  protected NrgiResponsiveImageHelperService $responsiveImageService;

  /**
   * The NRGI translation helper service.
   *
   * @var \Drupal\nrgi_frontend\Services\NrgiTranslationHelperService
   */
  protected NrgiTranslationHelperService $nrgiTranslationHelperService;

  /**
   * Constructs a new MetadataHelperService.
   *
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher interface.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter interface.
   * @param \Drupal\nrgi_frontend\Services\NrgiResponsiveImageHelperService $responsive_image_service
   *   The NRGI responsive image style helper service.
   * @param \Drupal\nrgi_frontend\Services\NrgiTranslationHelperService $nrgi_translation_helper_service
   *   The NRGI translation helper service.
   */
  public function __construct(
    PathMatcherInterface $path_matcher,
    DateFormatterInterface $date_formatter,
    NrgiResponsiveImageHelperService $responsive_image_service,
    NrgiTranslationHelperService $nrgi_translation_helper_service
  ) {
    $this->pathMatcher = $path_matcher;
    $this->dateFormatter = $date_formatter;
    $this->responsiveImageService = $responsive_image_service;
    $this->nrgiTranslationHelperService = $nrgi_translation_helper_service;
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
    /* Content type specific meta. */

    // Node header meta temporary array.
    $header_meta = [];

    switch ($node->bundle()) {
      case 'article':
      case 'publication':
        // Header download report PDF .
        $this->preprocessDownloads(
          $node,
          ['field_upload'],
          $header_meta,
          TRUE
        );
        $variables['report_pdf'] = $header_meta['files'][0] ?? NULL;

        // Authors (internal and external)
        $this->preprocessResourcesAuthors(
          $node,
          'field_author',
          'field_external_authors',
          $variables
        );

        // Header date.
        if ($date = $node->get('unified_date')) {
          $card_date = $this->dateFormatter
            ->format($date->value, 'resource_header_date');
          $variables['date'] = $card_date;
        }

        // Language switcher.
        $variables['language_switcher_links'] = $this
          ->nrgiTranslationHelperService->getLanguageSwitcherLinks(
            $node, FALSE
          );
        break;

      case 'event':
        $this->preprocessEventDetails(
          $node,
          $this->metadataFieldNames['event']['event_details'],
          $variables
        );
        break;
    }

    /* All node types meta. */
    $variables['subtype'] = $this->getTermLabels($node, $this->nodeSubtypeFields[$node->bundle()])[0];

    // Node footer meta.
    $this->preprocessLogos(
      $node,
      $this->metadataFieldNames['all']['logo'],
      t('Produced with financial support from'),
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
    $variables['content_type_label'] = $node->type->entity->label();

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
    $variables['translations'] = $this->nrgiTranslationHelperService
      ->getCardAvailableLanguagesString($node);

    // Topics.
    if ($topic = $this->getTermLabels($node, 'field_topic')) {
      $variables['topics'] = $this->getTermLabels($node, 'field_topic');
    }

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
    $this->preprocessLogos(
      $node,
      $this->metadataFieldNames['all']['sidebar_logo'],
      t('Produced in partnership with'),
      $variables,
      TRUE,
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
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
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
            if ($title_field = $node->get($metadata_field_name)->first()) {
              $metadata[] = [
                'label' => t('Top image'),
                'items' => [
                  [
                    'type' => 'text',
                    'title' => $title_field->value,
                  ],
                ],
              ];
            }
            break;
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
   * @param bool $items_only
   *   Whether to add items only to variables array, FALSE by default.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function preprocessDownloads(
    NodeInterface $node,
    array $download_field_names,
    array &$variables,
    bool $items_only = FALSE,
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
            foreach ($entity_fields as $entity) {
              if ($entity instanceof ParagraphInterface) {
                if ($entity->hasField('field_title')
                    && $document_label = $entity->get('field_title')) {
                  $document_label = $document_label->first()->value;
                }

                $media = $entity->get('field_file')->entity;
                if ($media instanceof MediaInterface) {
                  $items[] = $this->getFileFromMediaDocument($media, $document_label ?: '');
                }
              }
              elseif ($entity instanceof MediaInterface) {
                $items[] = $this->getFileFromMediaDocument($entity);
              }
            }
            break;
        }
      }
    }
    if ($items) {
      if (!$items_only) {
        $metadata = [
          'label' => t('Additional downloads'),
          'items' => $items,
        ];
        $variables['meta_data'][] = [$metadata];
      }
      else {
        $variables['files'] = $items;
      }
    }
  }

  /**
   * Preprocess logos.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   * @param String[] $logo_field_names
   *   Array of logo field names.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $section_label
   *   The metadata section label.
   * @param array $variables
   *   The variables array.
   * @param bool $sidebar
   *   Whether to add on metadata sidebar sub array, FALSE by default.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function preprocessLogos(
    NodeInterface $node,
    array $logo_field_names,
    TranslatableMarkup $section_label,
    array &$variables,
    bool $sidebar = FALSE
  ): void {
    $items = [];

    foreach ($logo_field_names as $logo_field_name) {
      if ($node->hasField($logo_field_name) && $field = $node->get($logo_field_name)) {
        if (!$field instanceof FieldItemList | $field->isEmpty()) {
          continue;
        }
        switch ($field->getFieldDefinition()->getType()) {
          case 'entity_reference':
          case 'entity_reference_revisions':
            $entity_fields = $field->referencedEntities();
            foreach ($entity_fields as $paragraph) {
              if ($paragraph instanceof ParagraphInterface) {
                $media = $paragraph->get('field_image')->entity;
                if ($media instanceof MediaInterface) {

                  $logo = [];

                  $this->responsiveImageService->preprocessResponsiveImage(
                    $media,
                    'square_small',
                    $logo);

                  if ($paragraph->hasField('field_link')
                      && $link_field = $paragraph->get('field_link')) {
                    $link_item = $link_field->first();
                    if ($link_item instanceof LinkItemInterface) {
                      $link = $link_item->getUrl()->toString();
                      $is_external = $link_item->getUrl()->isExternal();
                    }
                  }

                  if ($paragraph->hasField('field_title')
                      && $title_field = $paragraph->get('field_title')) {
                    $title = $title_field->getValue()[0]['value'];

                  }

                  $items[] = [
                    'type' => 'logo',
                    'title' => $title ?? NULL,
                    'link' => $link ?? NULL,
                    'is_external' => $is_external ?? NULL,
                    'logo' => $logo['responsive_image'] ?? NULL,
                  ];
                }
              }
            }
            break;
        }
      }
    }
    if ($items) {
      $metadata = [
        'label' => $section_label,
        'items' => $items,
      ];

      if ($sidebar) {
        $variables['sidebar_data'][] = [$metadata];
      }
      else {
        $variables['meta_data'][] = [$metadata];

      }
    }
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
              'items' => [
                'type' => 'text',
                'title' => $node->get($field_name)->first()->value,
              ],
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

    if ($event_type = $this->getTermLabels($node, 'field_event_type')) {
      $variables['subtype'] = $event_type[0];
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
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function preprocessResourceCardMetadata(
    NodeInterface $node,
    array &$variables,
  ): void {

    // Resource type.
    if ($resource_type = $this->getTermLabels(
      $node,
      'field_resource_type')
    ) {
      $variables['subtype'] = $resource_type[0];
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
   * Get the resource authors array.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The resource node.
   * @param string $internal_people_field_name
   *   The person reference(s) field name.
   * @param string $external_people_field_name
   *   The people paragraph(s) field name.
   * @param array $variables
   *   The variable array.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function preprocessResourcesAuthors(
    NodeInterface $node,
    string $internal_people_field_name,
    string $external_people_field_name,
    array &$variables
  ): void {

    $authors = [];

    /* Internal authors - reference to Person content type */
    if ($node->hasField($internal_people_field_name)
        && $person_field = $node->get($internal_people_field_name)) {
      if ($person_field instanceof EntityReferenceFieldItemListInterface) {
        foreach ($person_field as $person_reference_item) {
          $person = [];
          if ($person_reference_item instanceof EntityReferenceItem) {
            if ($person_reference = $person_reference_item->get('entity')) {
              if ($person_reference instanceof EntityReference) {
                if ($person_reference->getTarget()) {
                  $person_entity = $person_reference->getTarget()->getValue();
                  if ($person_entity instanceof NodeInterface
                      && $person_entity->bundle() == 'person') {
                    // Link to person.
                    $person['url'] = $person_entity->toUrl()->toString();

                    // Firstname & Surname.
                    if ($person_entity->hasField('field_given_name')
                        && $firstname = $person_entity->get('field_given_name')) {
                      $person['firstname'] = $firstname->value;
                    }
                    if ($person_entity->hasField('field_surname')
                        && $surname = $person_entity->get('field_surname')) {
                      $person['surname'] = $surname->value;
                    }

                    // Job title.
                    if ($person_entity->hasField('field_job_title')
                        && $job_title = $person_entity->get('field_job_title')) {
                      $person['job_title'] = $job_title->value;
                    }

                    // Headshot.
                    if ($person_entity->hasField('field_featured_image')
                        && $person_entity->get('field_featured_image')
                        && $media = $person_entity->get('field_featured_image')->entity) {
                      if ($media instanceof MediaInterface) {
                        /** @var  \Drupal\nrgi_frontend\Services\NrgiResponsiveImageHelperService $responsive_image_style_service */
                        $responsive_image_style_service = \Drupal::service('nrgi_frontend.responsive_image_helper');
                        $responsive_image_style_service->preprocessResponsiveImage(
                          $media,
                          'square_small',
                          $person
                        );
                      }
                    }
                  }
                }
              }
            }
          }
          $authors[] = $person;
        }
      }
    }

    /* External authors - Paragraph */
    if ($node->hasField($external_people_field_name)
        && $external_person_field = $node->get($external_people_field_name)) {
      foreach ($external_person_field as $external_person_reference_item) {
        $external_person = [];
        if ($external_person_reference_item instanceof EntityReferenceRevisionsItem) {
          if ($external_person_reference = $external_person_reference_item->get('entity')) {
            if ($external_person_reference instanceof EntityReferenceRevisions) {
              if ($external_person_reference->getTarget()) {
                $external_person_paragraph = $external_person_reference->getTarget()
                  ->getValue();
                if ($external_person_paragraph instanceof ParagraphInterface) {
                  if ($external_person_paragraph->bundle() == 'external_author') {
                    // Link to external profile.
                    if ($external_person_paragraph->hasField('field_external_author_link')
                        && $link_field = $external_person_paragraph->get('field_external_author_link')) {
                      if ($link_field->first() instanceof LinkItemInterface) {
                        $external_person['url'] = $link_field->first()->uri;
                        $external_person['is_external'] = TRUE;
                      }
                    }

                    // Firstname & Surname.
                    if ($external_person_paragraph->hasField('field_given_name')
                        && $firstname = $external_person_paragraph->get('field_given_name')) {
                      $external_person['firstname'] = $firstname->value;
                    }
                    if ($external_person_paragraph->hasField('field_surname')
                        && $surname = $external_person_paragraph->get('field_surname')) {
                      $external_person['surname'] = $surname->value;
                    }
                  }
                }
              }
            }
          }
        }
        $authors[] = $external_person;
      }
    }
    if (!empty($authors)) {
      $variables['authors'] = $authors;
    }
  }

  /**
   * Get selected term (entity) labels.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $taxonomy_field_name
   *   The taxonomy field name.
   *
   * @return array
   *   The terms selected labels, empty if not found.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getTermLabels(
    NodeInterface $node,
    string $taxonomy_field_name,
  ): array {
    $labels = [];
    if ($node->hasField($taxonomy_field_name)
        && $taxonomy_field = $node->get($taxonomy_field_name)) {
      foreach ($taxonomy_field as $entity_reference_item) {
        if ($entity_reference_item instanceof EntityReferenceItem) {
          if ($entity_reference = $entity_reference_item->get('entity')) {
            if ($entity_reference instanceof EntityReference) {
              if ($entity_reference->getTarget()) {
                $entity = $entity_reference->getTarget()
                  ->getValue();
                if ($entity instanceof TermInterface) {
                  $labels[] = $entity->getName();
                }
                elseif ($entity instanceof NodeInterface) {
                  $labels[] = $entity->label();
                }
              }
            }
          }
        }
      }
    }
    return $labels;
  }

  /**
   * Gets a file array from Media document.
   *
   * @param \Drupal\media\MediaInterface $media
   *   Media item to get file from.
   * @param string $document_label
   *   The document label, empty string by default.
   *
   * @return array|null
   *   File array.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getFileFromMediaDocument(MediaInterface $media, string $document_label = ''): ?array {

    /** @var \Drupal\file\Entity\File $file */
    $file = $media->hasField('field_media_document')
            && !($media->get('field_media_document')->isEmpty())
      ? $media->get('field_media_document')->entity : NULL;

    if ($file instanceof FileInterface) {
      $file_size = format_size($file->getSize());
      $file_type = $file->getMimeType();
      $file_type = strtoupper(explode('/', $file_type,)[1]);
      if (!$document_label) {
        $document_label =
          $media->hasField('field_media_label')
          && !$media->get('field_media_label')->isEmpty()
          && ($media->get('field_media_label')
              && $file_label = $media->get('field_media_label')->first()->value)
            ? $file_label
            : $file->getFilename();
      }
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
