<?php

namespace Drupal\nrgi_layout_builder\Plugin\Layout;

/**
 * Provides a plugin class for one column layouts.
 */
final class OneColumnLayout extends LayoutBase {

  /**
   * {@inheritdoc}
   */
  protected function getColumnWidths(): array {
    return [
      '100' => $this->t('100%'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultColumnWidth(): string {
    return '100';
  }
}
