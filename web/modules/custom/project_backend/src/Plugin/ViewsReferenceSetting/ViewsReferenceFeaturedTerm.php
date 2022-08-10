<?php

namespace Drupal\project_backend\Plugin\ViewsReferenceSetting;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\ViewExecutable;
use Drupal\viewsreference\Plugin\ViewsReferenceSettingInterface;

/**
 * The views reference setting for taxonomy terms.
 * The contextual filter for taxonomy terms must be enabled in the view.
 * In the paragraph, the field_dynamic_listing has to have the "Featured content - Taxonomy term" setting enabled.
 *
 * @ViewsReferenceSetting(
 *   id = "featured_term",
 *   label = @Translation("NRGI Featured content - Taxonomy term"),
 *   default_value = 0,
 * )
 */
class ViewsReferenceFeaturedTerm extends PluginBase implements ViewsReferenceSettingInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function alterFormField(array &$form_field) {
    // Get all the taxonomy term ids and labels.
    $vocabularies = ['topic', 'country', 'language', 'content_type_label'];
    $taxonomyTerms = [];
    $terms = Term::loadMultiple();
    foreach ($terms as $term) {
      if (in_array($term->bundle(), $vocabularies)) {
        $taxonomyTerms[$term->id()] = $term->getName();
      }
    }
    // Create a select field.
    $form_field['#type'] = 'select';
    $form_field['#title'] = 'Term';
    $form_field['#multiple'] = true;
    $form_field['#options'] = $taxonomyTerms;
    $form_field['#weight'] = 35;
  }

  /**
   * {@inheritdoc}
   */
  public function alterView(ViewExecutable $view, $value) {
    // Get the current view and the selected taxonomy terms.
    $display = $view->storage->getDisplay($view->current_display);
    $arguments = $display["display_options"]["arguments"];
    $i = 0;
    foreach ($arguments as $argument) {
      if ($argument["id"] == 'field_taxonomy_term_target_id') {
        $key = $i;
        break;
      }
      $i++;
    }
    // Send the selected taxonomy terms to the view.
    if (isset($key)) {
      $valueString = implode('+', $value);
      $arg[$key] = $valueString;
      $view->setArguments($arg);
    }
  }

}
