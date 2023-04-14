<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\nrgi_frontend\NrgiFeaturedCardsBase;

/**
 * Class NrgiFeaturedPageService.
 */
class NrgiFeaturedPagesHelperService extends NrgiFeaturedCardsBase {

  /**
   * Handle preprocess Paragraph for featured cards.
   *
   * @param array $variables
   *   Variables from hook_preprocess_paragraph().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function preprocessFeaturedPages(array &$variables): void {
    $this->setParagraph($variables['paragraph']);

    if (!empty($this->getNodeIds()) && $page_nids = $this->getNodeIds()) {
      $this->setViewMode('featured_page');
      // Get node storage instance.
      $storage = $this->entityTypeManager->getStorage('node');
      $page_nodes = $storage->loadMultiple($page_nids);

      $variables['featured_pages'] = [
        'items' => $this->renderFeaturedCards($page_nodes),
        'with_image' => $this->withImage,
        'view_mode' => $this->viewMode,
        'layout' => $this->paragraph->get($this->itemsPerRowField)->getString(),
        'cta' => $this->paragraph->hasField($this->linkField)
          ? $this->paragraphButtonLinkHelperService->getLinkFieldValues(
            $this->paragraph,
            $this->linkField)
          : '',
      ];
    }
  }

}
