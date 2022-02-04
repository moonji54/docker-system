<?php

namespace Drupal\nrgi_layout_builder\Plugin\Layout;

/**
 * Provides a plugin class for three column layouts.
 */
final class ThreeColumnLayout extends LayoutBase {

  /**
   * {@inheritdoc}
   */
  protected function getColumnWidths(): array {
    return [
      '33-33-33' => $this->t('33% - 33% - 33%'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultColumnWidth(): string {
    return '33-33-33';
  }
}
