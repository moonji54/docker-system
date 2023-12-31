<?php

/**
 * @file
 * Contains nrgi_frontend.paragraph.inc.
 */

/**
 * Implements hook_preprocess_paragraph().
 */
function nrgi_frontend_preprocess_paragraph(&$variables) {
  /** @var \Drupal\paragraphs\Entity\Paragraph $paragraph */
  $paragraph = $variables['paragraph'];
  $node = $paragraph->getParentEntity();

  // Adding node information as a render variable to paragraphs.
  $variables['node'] = $node;

  // If paragraph rendered on page to PDF.
  $route_name = \Drupal::routeMatch()->getRouteName();
  if ($route_name == 'soapbox_pdf.node_pdf_page_controller') {
    $variables['is_pdf'] = TRUE;
    $variables['fallback_text_link'] = t('To learn more, visit') . ' ';
    $variables['fallback_link'] = $node->toUrl()->toString();
  }

  // Add details for specific paragraph types.
  switch ($paragraph->getType()) {
    case 'featured_content':
      /** @var \Drupal\nrgi_frontend\Services\NrgiFeaturedContentHelperService $paragraph_featured_content_service */
      $paragraph_featured_content_service = \Drupal::service('nrgi_frontend.paragraph_featured_content_helper');

      // Preprocess featured pages.
      $paragraph_featured_content_service->preprocessFeaturedContent($variables);
      break;

    case 'featured_pages':
      /** @var \Drupal\nrgi_frontend\Services\NrgiFeaturedPagesHelperService $paragraph_featured_pages_service */
      $paragraph_featured_pages_service = \Drupal::service('nrgi_frontend.paragraph_featured_pages_helper');

      // Preprocess featured pages.
      $paragraph_featured_pages_service->preprocessFeaturedPages($variables);
      break;

    case 'featured_people':
      /** @var \Drupal\nrgi_frontend\Services\NrgiFeaturedPeopleHelperService $paragraph_featured_people_service */
      $paragraph_featured_people_service = \Drupal::service('nrgi_frontend.paragraph_featured_people_helper');

      // Preprocess featured pages.
      $paragraph_featured_people_service->preprocessFeaturedPeople($variables);
      break;

    case 'call_to_action':
    case 'learning_block':
      /** @var \Drupal\nrgi_frontend\Services\NrgiParagraphButtonLinkHelperService $paragraph_button_link_helper_service */
      $paragraph_button_link_helper_service = \Drupal::service('nrgi_frontend.paragraph_button_link_helper');

      $variables['button_link'] = $paragraph_button_link_helper_service
        ->getButtonLinkArray(
          $paragraph,
          'field_link',
          'field_file',
          'field_download_button_label',
          'field_type'
        );
      break;

    case 'wysiwyg':
      if ($paragraph->hasField('field_is_concluding_statement')
          && $is_concluding = $paragraph->get('field_is_concluding_statement')) {
        $variables['is_concluding'] = $is_concluding->value;
      }
      break;
  }
}
