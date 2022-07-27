<?php

namespace Drupal\project_backend\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_tables",
 *   title = @Translation("Tables div filter"),
 *   description = @Translation("Surrounds tables with a div"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class TablesFilter extends FilterBase {

  public function process($text, $langcode) {
    $text = str_replace('<table>', "<div class='resposive-table'><table>", $text);
    $text = str_replace('</table>', "</table></div>", $text);
    return new FilterProcessResult($text);
  }
}
