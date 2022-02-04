<?php

namespace Drupal\nrgi_layout_builder\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;

/**
 * Provides a layout base for custom layouts.
 */
abstract class LayoutBase extends LayoutDefault {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $column_widths = $this->getColumnWidths();

    if (!empty($column_widths)) {
      $form['layout'] = [
        '#type' => 'details',
        '#title' => $this->t('Layout'),
        '#open' => TRUE,
        '#weight' => 30,
      ];

      $form['layout']['column_width'] = [
        '#type' => 'radios',
        '#title' => $this->t('Column Width'),
        '#options' => $column_widths,
        '#default_value' => $this->configuration['column_width'] ?? $this->getDefaultColumnWidth(),
        '#required' => TRUE,
      ];
    }

    $form['#attached']['library'][] = 'nrgi_layout_builder/layout-builder-grid';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['column_width'] = $values['layout']['column_width'];
  }

  /**
   * Get the column widths.
   *
   * @return array
   *   The column widths.
   */
  abstract protected function getColumnWidths(): array;

  /**
   * Get the default column width.
   *
   * @return string
   *   The default column width.
   */
  abstract protected function getDefaultColumnWidth(): string;

}
