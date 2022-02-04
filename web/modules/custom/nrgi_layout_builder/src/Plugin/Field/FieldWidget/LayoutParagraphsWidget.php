<?php

namespace Drupal\nrgi_layout_builder\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_paragraphs_restriction\Plugin\Field\FieldWidget\LayoutParagraphsRestrictionWidget;

/**
 * NRGI Entity Reference with Layout field widget.
 *
 * @FieldWidget(
 *   id = "nrgi_layout_paragraphs",
 *   label = @Translation("NRGI Layout Paragraphs"),
 *   description = @Translation("Layout builder for paragraphs with NRGI
 *   extensions."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   },
 * )
 */
class LayoutParagraphsWidget extends LayoutParagraphsRestrictionWidget {

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $elements = parent::form($items, $form, $form_state, $get_delta);
    $form['#attached']['library'][] = 'nrgi_layout_builder/layout-builder-expand';
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $form['#attached']['library'][] = 'nrgi_layout_builder/layout-builder-expand';
    return $element;
  }

  /**
   * Builds the main widget form array container/wrapper.
   *
   * Form elements for individual items are built by formElement().
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);
    $elements['expand'] = [
      '#type' => 'button',
      '#value' => ['Expand'],
      '#attributes' => [
        'title' => ['Expand the width of the editing area to easier visually edit the content.'],
        'class' => ['c-layout-expand', 'js-layout-expand'],
      ],
      '#weight' => -1,
    ];
    $elements['collapse'] = [
      '#type' => 'button',
      '#value' => ['Collapse'],
      '#attributes' => [
        'title' => ['Collapse the width of the editing area to view the sidebar again.'],
        'class' => ['c-layout-collapse', 'js-layout-collapse'],
      ],
      '#weight' => -1,
    ];
    return $elements;
  }

}
