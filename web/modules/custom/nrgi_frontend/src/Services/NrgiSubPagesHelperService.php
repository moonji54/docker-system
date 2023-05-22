<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\soapbox_nodes\SubPagesHelperService;

/**
 * Class to help render subpages.
 *
 * @package Drupal\soapbox_nodes
 */
class NrgiSubPagesHelperService extends SubPagesHelperService {

  /**
   * Get sub menu items.
   *
   * @param string|int $parent
   *   Parent menu item.
   *
   * @return array
   *   Array of sub menu items.
   */
  public function getSubMenuItems($parent): array {
    // Overriding parent method to include menu items that are disabled,
    // so the editors can choose to disable the menu item but still show
    // it on the sub-nav.
    // Gets the items in the same order as they are specified in the menu.
    $query = \Drupal::entityQuery('menu_link_content')
      ->condition('parent', "menu_link_content:{$parent}")
      ->sort('weight', 'ASC')
      ->sort('title', 'ASC');

    return $query->execute();
  }

}
