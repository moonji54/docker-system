<?php

namespace Drupal\project_backend\Plugin\ViewsReferenceSetting;

use Drupal;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;

/**
 * The views reference setting for content types.
 * The contextual filter for content types must be enabled in the view.
 * In the paragraph, the field_dynamic_listing has to have the "NRGI Featured content - Content type" setting enabled.
 *
 * @ViewsReferenceSetting(
 *   id = "featured_content_type",
 *   label = @Translation("NRGI Featured content - Content type"),
 *   default_value = 0,
 * )
 */
class ViewsReferenceFeaturedContentType extends PluginBase implements ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(array &$form_field) {
    // Get all the content type ids and labels.
    $entityTypeManager = Drupal::service('entity_type.manager');
    $contentTypes = [];
    $content = $entityTypeManager->getStorage('node_type')->loadMultiple();
    foreach ($content as $contentType) {
      if ($contentType->id() != 'person') {
        $contentTypes[$contentType->id()] = $contentType->label();
      }
    }
    // Create a select field.
    $form_field['#type'] = 'select';
    $form_field['#title'] = 'Content type';
    $form_field['#multiple'] = true;
    $form_field['#options'] = $contentTypes;
    $form_field['#weight'] = 35;
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    // Get the current view and the selected content types.
    $display = $view->storage->getDisplay($view->current_display);
    if (key_exists('arguments', $display['display_options'])) {
      $arguments = $display["display_options"]["arguments"];
      $i = 0;
      foreach ($arguments as $argument) {
        if ($argument["id"] == 'type') {
          $key = $i;
          break;
        }
        $i++;
      }
    }

    // Send the selected content types to the view.
    if (isset($key)) {
      $valueString = implode('+', $value);
      $arg[$key] = $valueString;
      $view->setArguments($arg);
    }
  }

}
