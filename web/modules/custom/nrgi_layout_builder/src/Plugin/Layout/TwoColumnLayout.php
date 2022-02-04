<?php

namespace Drupal\nrgi_layout_builder\Plugin\Layout;

/**
 * Provides a plugin class for two column layouts.
 */
final class TwoColumnLayout extends LayoutBase {

  /**
   * {@inheritdoc}
   */
  protected function getColumnWidths(): array {
    return [
      '33-67' => $this->t('33% - 67%'),
      '67-33' => $this->t('67% - 33%'),
      '50-50' => $this->t('50% - 50%'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultColumnWidth(): string {
    return '50-50';
  }
}
