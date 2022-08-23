<?php

/**
 * @file
 * Contains project_frontend.node.inc.
 */

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\Entity\Term;

function _project_frontend_get_meta_fields(NodeInterface $node) {
  // SBTODO: Adjust fields that appear in meta.

  // Fields that begin with an _underscore are given special treatment.
  $fields = [
    'field_author'               => 'Contact',
    'field_partners'             => 'Partners',
    '_publication_name'          => 'Publication name',
    '_publisher_and_link'        => 'Publisher',
    'field_topic'                => 'Topic',
    'field_keywords'             => 'Keyword',
    'field_country'              => 'Country',
    'field_region'               => 'Region',
    'field_supporting_document'  => 'Additional downloads',
    'field_data_document'        => 'Additional downloads',
    'field_photo_caption'        => 'Featured image photo caption',
  ];

  $content_type = $node->bundle();

  if ($content_type === 'event') {
    $fields = array_merge([
      'field_event_contact'       => 'Event contact',
      'field_course_syilabus'     => 'Course syllabus',
      'field_expertise_required'  => 'Expertise required',
      'field_address'             => 'Address',
      '_event_date'               => 'Date and time',
      '_event_location'           => 'Location',
    ], $fields);
  }

  return $fields;
}

/**
 * Get node tags form fields/taxonomies in an array format.
 *
 * @param array $variables
 *   Array of template variables.
 * @param \Drupal\node\NodeInterface $node
 *   Node to get data from.
 *
 * @throws \Drupal\Core\Entity\EntityMalformedException
 */
function _project_frontend_preprocess_node_meta(array &$variables, NodeInterface $node) {
  $meta_fields = _project_frontend_get_meta_fields($node);
  $meta        = [];

  foreach ($meta_fields as $field_name => $meta_label) {
    if ($node->hasField($field_name)) {
      $meta_field_data = $node->get($field_name);

      if (!empty($meta_field_data) && count($meta_field_data)) {
        $meta[$field_name]['label'] = $meta_label;

        /** @var \Drupal\field\Entity\FieldConfig $field_config */
        $field_config = $meta_field_data->getFieldDefinition();
        $field_type   = $field_config->getType();

        switch ($field_type) {
          case 'datetime':
            _project_frontend_processs_datetime_fields($field_name, $meta_field_data, $meta);
            break;

          case 'entity_reference':
            _project_frontend_process_entity_reference_fields($field_name, $meta_field_data, $meta);
            break;

          case 'entity_reference_revisions':
            _project_frontend_process_paragraph_fields($field_name, $meta_field_data, $meta);
            break;

          case 'language':
            _project_frontend_process_language_fields($field_name, $meta_field_data, $meta);
            break;

          case 'link':
            _project_frontend_process_link_fields($field_name, $meta_field_data, $meta);
            break;

          case 'string':
          default:
            $meta[$field_name]['values'][] = [
              'text' => $meta_field_data->getString(),
            ];
            break;
        }
      }
    }
    else {
      // Special cases or combinations of fields.
      switch ($field_name) {
        case '_event_date':
        case '_project_date':
          _project_frontend_process_date_range_fields($node, $field_name, $meta_label, $meta);
          break;

        case '_event_location':
          _project_frontend_process_event_location_fields($node, $field_name, $meta_label, $meta);
          break;
      }
    }
  }

  if (!empty($meta)) {
    // Return all the meta to the template.
    $variables['all_meta'] = $meta;

    // Split meta into top/bottom.
    $top_meta_keys = [
      '_event_date',
      '_event_location',
      'field_author',
      'field_partners',
      'field_publication_name',
      '_publication_name',
      '_publisher_and_link',
      'field_date',
    ];

    $bottom_meta_keys         = array_diff(array_keys($meta_fields), $top_meta_keys);
    $variables['top_meta']    = array_intersect_key($meta, array_flip($top_meta_keys));
    $variables['bottom_meta'] = array_intersect_key($meta, array_flip($bottom_meta_keys));
  }
}

function _project_frontend_process_entity_reference_fields($field_name, $meta_field_data, &$meta) {
  /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $meta_field_data */
  if ($meta_field_data->count()) {
    $entities = $meta_field_data->referencedEntities();

    foreach ($entities as $entity) {
      if ($entity instanceof Node) {
        /** @var \Drupal\node\Entity\Node $entity */
        if ($entity->bundle() !== 'organisation') {
          $meta[$field_name]['values'][] = [
            'text' => $entity->getTitle(),
            'url'  => $entity->toUrl()->toString(),
          ];
        }
        else {
          $meta[$field_name]['values'][] = [
            'text' => $entity->getTitle(),
          ];
        }
      }

      if ($entity instanceof Term) {
        /** @var \Drupal\taxonomy\Entity\Term $entity */
        if ($entity->hasField('field_landing_page')
          && $landing_url = $entity->get('field_landing_page')->getValue()) {
          $meta[$field_name]['values'][] = [
            'text' => $entity->getName(),
            'url'  => str_replace('entity:', '/', $landing_url['0']['uri']),
          ];
        }
        else {
          $meta[$field_name]['values'][] = [
            'text' => $entity->getName(),
            'url'  => $entity->toUrl()->toString(),
          ];
        }
      }

      if ($entity instanceof Media) {
        $fid   = $entity->getSource()->getSourceFieldValue($entity);
        $file  = \Drupal\file\Entity\File::load($fid);
        $label = '';
        $url   = $file->createFileUrl();

        if ($entity->hasField('field_label')) {
          /** @var \Drupal\Core\Field\FieldItemList $label */
          $label = $entity->get('field_label')->getString();
        }

        if (empty($label)) {
          $label = $entity->getName();
        }

        $extra_file_meta = '';
        $file_size       = format_size($file->getSize());
        $file_pathinfo   = pathinfo($url);

        if (!empty($file_pathinfo['extension']) || !empty($file_size)) {
          $extra_file_meta .= ' (';

          if (!empty($file_pathinfo['extension'])) {
            $extra_file_meta .= strtoupper($file_pathinfo['extension']);
          }

          if (!empty($file_pathinfo) && !empty($file_size)) {
            $extra_file_meta .= ' ';
          }

          if (!empty($file_size)) {
            $extra_file_meta .= str_replace(' ', '', $file_size);
          }

          $extra_file_meta .= ')';
        }

        /** @var \Drupal\media\Entity\Media $entity */
        $meta[$field_name]['values'][] = [
          'text' => $label . $extra_file_meta,
          'url'  => $url,
        ];
      }
    }
  }
}


function _project_frontend_process_paragraph_fields($field_name, $meta_field_data, &$meta) {
  if ($field_name === 'field_organisations') {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $organisations_field */
    $organisations_field = $meta_field_data;

    if (!empty($organisations_field)) {
      foreach ($organisations_field as $affiliation) {
        /** @var \Drupal\paragraphs\Entity\Paragraph $publications_paragraph */
        $affiliation_paragraph = $affiliation->entity;

        if ($affiliation_paragraph instanceof Paragraph) {
          // Get the organisation name from the entity.
          $organisation_entities = $affiliation_paragraph->get('field_organisation')->referencedEntities();
          $organisation_name     = '';

          if (!empty($organisation_entities[0])) {
            if ($organisation_entities[0] instanceof Node) {
              /** @var \Drupal\node\Entity\Node $entity */
              $organisation_name = $organisation_entities[0]->getTitle();
            }
          }
        }

        $meta[$field_name]['values'][] = [
          'text' => $organisation_name,
        ];
      }
    }
  }
}

function _project_frontend_processs_datetime_fields($field_name, $meta_field_data, &$meta) {
  /** @var \Drupal\Core\Datetime\DrupalDateTime $date */
  $date = $meta_field_data->date;

  if ($date instanceof DrupalDateTime) {
    $meta[$field_name]['values'][] = [
      'text' => \Drupal::service('date.formatter')
                       ->format($date->getTimestamp(), 'project_date'),
    ];
  }
}

function _project_frontend_process_language_fields($field_name, $meta_field_data, &$meta) {
  $languages = \Drupal::service('language_manager')->getStandardLanguageList();
  $langcode  = $meta_field_data->getString();

  if (array_key_exists($langcode, $languages)) {
    $meta[$field_name]['values'][] = [
      'text' => $languages[$langcode][0],
      // `0` = name in default lang, `1` = name in native language.
    ];
  }
}

function _project_frontend_process_link_fields($field_name, $meta_field_data, &$meta) {
  /** @var \Drupal\Core\Field\FieldItemList $meta_field_data */
  if ($meta_field_data->count()) {
    $links = $meta_field_data->getValue();

    foreach ($links as $link) {
      /** @var \Drupal\node\Entity\Node $entity */
      $meta[$field_name]['values'][] = [
        'text' => $link['title'],
        'url'  => $link['uri'],
      ];
    }
  }
}

function _project_frontend_process_date_range_fields($node, $field_name, $meta_label, &$meta) {
  if ($node->hasField('field_start_date')) {
    /** @var \Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList $start_date */
    $start_date = $node->get('field_start_date');

    /** @var \Drupal\datetime\Plugin\Field\FieldType\DateTimeFieldItemList $end_date */
    $end_date = $node->get('field_end_date');

    $date_string = _prepare_date_range_string($start_date->date, $end_date->date);

    if (!empty($date_string)) {
      $meta[$field_name]['label']    = $meta_label;
      $meta[$field_name]['values'][] = [
        'text' => $date_string,
      ];
    }
  }
}

function _project_frontend_process_event_location_fields($node, $field_name, $meta_label, &$meta) {
  $array = _project_frontend_process_field_and_link($node, 'field_location', 'field_map_link', 'Map');

  if (!empty($array)) {
    $array['label'] = $meta_label;
  }

  // Add to meta array.
  $meta[$field_name] = $array;
}

function _project_frontend_process_field_and_link($node, $text_field, $link_field, $fallback_text) {
  $array = [];

  if ($node->hasField($text_field)) {
    $text = $node->get($text_field)->getString();
  }

  if ($node->hasField($link_field)) {
    $link = $node->get($link_field)->getString();
  }

  if (!empty($text)) {
    $array['values'][0]['text'] = $text;
  }
  else {
    if (!empty($link)) {
      $array['values'][0]['text'] = $fallback_text;
    }
  }

  if (!empty($link)) {
    $array['values'][0]['url']    = $link;
    $array['values'][0]['target'] = '_blank';
  }

  return $array;
}

/**
 * Prepare a date range string to be output.
 *
 * @param \Drupal\Core\Datetime\DrupalDateTime $start_date
 *   DrupalDateTime of the start date of the range.
 * @param \Drupal\Core\Datetime\DrupalDateTime $end_date
 *   DrupalDateTime of the end date of the range.
 *
 * @return string
 *   Formatted date string.
 */
function _prepare_date_range_string($start_date, $end_date): string {
  // We have both dates.
  if ($start_date instanceof DrupalDateTime && $end_date instanceof DrupalDateTime) {
    if ($start_date->format('Y') !== $end_date->format('Y')) {
      return $start_date->format('j F Y') . ' – ' . $end_date->format('j F Y');
    }

    if ($start_date->format('m') !== $end_date->format('m')) {
      return $start_date->format('j F') . ' – ' . $end_date->format('j F Y');
    }

    if ($start_date->format('d') !== $end_date->format('d')) {
      return $start_date->format('j') . '–' . $end_date->format('j F Y');
    }

    return $start_date->format('j F Y H:i') . '-' . $end_date->format('H:i');
  }

  // We only have a start date.
  if ($start_date instanceof DrupalDateTime && empty($end_date)) {
    return $start_date->format('j F Y H:i');
  }

  // We have nothing to work with.
  return '';
}
