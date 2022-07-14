<?php

/**
 * @file
 * Definition of Drupal\bruegel_frontend\Plugin\views\filter\UnifiedDateYear.
 */

namespace Drupal\bruegel_frontend\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * Filters by given list of node title options.
 *
 * @ingroup views_filter_handlers
 * @ViewsFilter("unified_date_year")
 */
class UnifiedDateYear extends InOperator {

  /**
   * Array_filter callback to ensure safe years/
   *
   * @param int $year The year value.
   *
   * @return bool Whether it is actually a safe year.
   */
  public static function validateYear($year): bool {

    if ($year >= 1000 && $year <= 9999 && (int) $year == $year) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function init(
    ViewExecutable    $view,
    DisplayPluginBase $display,
    array             &$options = NULL
  ) {

    parent::init($view, $display, $options);
    $this->valueTitle                     = t('Allowed years');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Override the query so that no filtering takes place if the user doesn't
   * select any options.
   */
  public function query() {

    if (!empty($this->value)) {
      parent::query();
    }
  }

  /**
   * Skip validation if no options have been chosen so we can use it as a
   * non-filter.
   */
  public function validate() {

    if (!empty($this->value)) {
      parent::validate();
    }
  }

  /**
   * Provide a list of all the numeric operators
   */
  public function operatorOptions($which = 'title'): array {

    $options        = [];
    $this->operator = $this->operator ?? 'in';

    foreach ($this->operators() as $id => $info) {
      $options[$id] = $info[$which];
    }

    return $options;
  }

  /**
   * Better exposed filters is expecting 'in' 'or' or 'and' as the operator.
   *
   * @return array
   */
  public function operators(): array {

    // Keys are operators, method is the callback where we modify the query.
    return [
      'in' => [
        'title'  => $this->t('Is in'),
        'method' => 'opIn',
        'short'  => $this->t('in'),
        'values' => 1,
      ],
    ];
  }

  /**
   * Sets the allowed options from the generated options.
   */
  public function getValueOptions() {
    $this->valueOptions = $this->generateOptions();
  }

  /**
   * Helper function that generates the options.
   *
   * @param bool|array $bundles
   * @param bool|array $content_labels
   *
   * @return array
   */
  public function generateOptions(bool|array $bundles = [], bool|array $content_labels = []): array {

    // Get the range of unified_dates.
    $connection = \Drupal::database();
    $query      = $connection->select('node_field_data', 'nfd');
    $query->addExpression('MIN(nfd.unified_date)', 'min_unified_date');
    $query->addExpression('MAX(nfd.unified_date)', 'max_unified_date');
    $query->condition('unified_date', 0, '>');

    // If we have limited content type bundles.
    if ($bundles) {
      $query->condition('type', $bundles, 'IN');
    }

    if ($content_labels) {
      $query->join('node__field_content_label', 'nfcl', 'nfd.nid = nfcl.entity_id AND nfd.vid = nfcl.revision_id');
      $query->condition('field_content_label_target_id', $content_labels, 'IN');
    }

    $query->range(0, 1);
    $results = $query->execute()->fetchAssoc();

    // Return the lowest to highest year.
    if ($results) {
      $min_year     = date('Y', $results['min_unified_date']);
      $max_year     = date('Y', $results['max_unified_date']);
      $current_year = date('Y');

      // Don't allow a future year
      if ($max_year > $current_year) {
        $max_year = $current_year;
      }

      $years = range($min_year, $max_year);
      return array_combine($years, $years);
    }

    return [];
  }

  /**
   * The form which is potentially also shown to visitor as exposed form.
   *
   * @param                                      $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $types          = FALSE;
    $content_labels = FALSE;

    // Ideally only get options for the selected content type(s) or content label(s).
    if ($other_filters = $this->view->getHandlers('filter')) {
      if (isset($other_filters['type']) && isset($other_filters['type']['value'])) {
        $types = $other_filters['type']['value'];
      }

      if (isset($other_filters['field_content_label_target_id']) && isset($other_filters['field_content_label_target_id']['value'])) {
        $content_labels = $other_filters['field_content_label_target_id']['value'];
      }
    }

    $options = $this->generateOptions($types, $content_labels);

    $form['value'] = [
      '#type'     => 'checkboxes',
      '#multiple' => TRUE,
      '#title'    => $this->t('Years'),
      '#options'  => $options,
    ];
  }

  /**
   * Callback from operators; modify the query to filter by year.
   */
  protected function opIn() {
    if ($this->operator == 'in') {
      // Get values from form submission.
      $years = array_values($this->value);

      // Ensure years strings are safe as we are doing a raw sql query.
      $years = array_filter($years, [$this, 'validateYear']);
      $years = array_filter($years);

      // Add to where query if we have years.
      if ($years) {
        if (method_exists($this->query, 'addWhereExpression')) {

          // Simple Year expression.
          $this->query->addWhereExpression($this->options['group'],
            'YEAR(FROM_UNIXTIME(`' . $this->table . '`.`unified_date`)) IN (' . implode(',',
              $years) . ')');
        }
        else {
          // Condition groups for timestamps.
          $or_group = $this->query->createConditionGroup('OR');

          foreach ($years as $year) {
            $start_time     = strtotime('00:01:01 January 1st, ' . $year);
            $end_time       = strtotime('23:59:59 December 31st, ' . $year);
            $year_condition = $this->query->createConditionGroup('AND')
                                          ->addCondition($this->realField, $start_time, '>=')
                                          ->addCondition($this->realField, $end_time, '<=');
            $or_group->addConditionGroup($year_condition);
          }

          // Add condition group.
          $this->query->addConditionGroup($or_group);
        }
      }
    }
  }

}
