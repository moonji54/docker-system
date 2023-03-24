<?php

namespace Drupal\nrgi_backend\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Surrounds tables with a div.
 *
 * @Filter(
 *   id = "filter_tables",
 *   title = @Translation("Tables div filter"),
 *   description = @Translation("Surrounds tables with a div"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class TablesFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $text = str_replace('<table>', "<div class='responsive-table'><table>", $text);
    $text = str_replace('</table>', "</table></div>", $text);
    return new FilterProcessResult($text);
  }

}
