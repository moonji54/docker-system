<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class providing helper methods for paragraph button links.
 */
class NrgiParagraphButtonLinkHelperService {

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  protected FileUrlGenerator $fileUrlGenerator;

  /**
   * NrgiParagraphButtonLinkHelperService constructor.
   *
   * @param \Drupal\Core\File\FileUrlGenerator $file_url_generator
   *   The file URL generator service.
   */
  public function __construct(
    FileUrlGenerator $file_url_generator
  ) {
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * Get button link array depending on link type (link or media download).
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph containing the link field.
   * @param string $link_field_name
   *   The link field name.
   * @param string $media_field_name
   *   The media field name.
   * @param string $media_button_link_label_field_name
   *   The field containing the label to use for the media download button.
   * @param string $link_type_field_name
   *   The link type field name.
   *
   * @return array
   *   The button link array. Empty if not available
   */
  public function getButtonLinkArray(
    ParagraphInterface $paragraph,
    string $link_field_name,
    string $media_field_name,
    string $media_button_link_label_field_name,
    string $link_type_field_name): array {

    $button_link_array = [];

    $link_type = $paragraph->get($link_type_field_name)->value;

    if ($link_type === 'link') {
      $button_link_array = $this->getLinkFieldValues($paragraph, $link_field_name);
    }

    elseif ($link_type === 'download') {
      $button_link_array = $this->getDocumentMediaFieldValues($paragraph, $media_field_name, $media_button_link_label_field_name);
    }

    // Add the field type on the array,.
    $button_link_array['type'] = $link_type;

    return $button_link_array;
  }

  /**
   * Get link array for link field type.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph containing the link field.
   * @param string $link_field_name
   *   The name of the link field.
   *
   * @return array|null
   *   Returns array if field is populated, NULL otherwise.
   */
  public function getLinkFieldValues(
    ParagraphInterface $paragraph,
    string $link_field_name
  ): ?array {
    $link_field = (
      $paragraph->hasField($link_field_name)
      && $paragraph->get($link_field_name)
    ) ? $paragraph->get($link_field_name)[0] : NULL;
    if (!$link_field) {
      return NULL;
    }

    return [
      'url' => $link_field->getUrl()->toString(),
      'is_external' => $link_field->isExternal(),
      'title' => $link_field->get('title')->getString(),
    ];
  }

  /**
   * Get link array for media field type.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph containing the link field.
   * @param string $media_field_name
   *   The media field name.
   * @param string $media_button_link_label_field_name
   *   The field containing the label to use for the media download button.
   *
   * @return array|null
   *   Returns array if field is populated, NULL otherwise.
   */
  public function getDocumentMediaFieldValues(
    ParagraphInterface $paragraph,
    string $media_field_name,
    string $media_button_link_label_field_name,
  ): ?array {
    if ($paragraph->hasField($media_field_name)
        && $media_field = $paragraph->get($media_field_name)) {
      if ($media_field instanceof EntityReferenceFieldItemListInterface) {
        /** @var \Drupal\Core\Entity\EntityInterface[] $media */
        if ($media = $media_field->referencedEntities()) {
          $media = reset($media);
          $document_uri = $media->field_media_document[0]->entity->getFileUri();
          $document_url = $this->fileUrlGenerator
            ->generateAbsoluteString($document_uri);
          if ($paragraph->hasField($media_button_link_label_field_name)
              && $label_field = $paragraph->get($media_button_link_label_field_name)) {
            $title = $label_field->getString();
          }

          return [
            'url' => $document_url,
            'is_external' => TRUE,
            'title' => $title ?: t('Download'),
          ];
        }
      }
    }
    return NULL;

  }

}
