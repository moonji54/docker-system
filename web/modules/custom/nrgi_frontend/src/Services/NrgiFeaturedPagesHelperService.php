<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class NrgiFeaturedPageService.
 */
class NrgiFeaturedPagesHelperService {

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * The paragraph button link helper service.
   *
   * @var \Drupal\Services\nrgi_frontend\NrgiParagraphButtonLinkHelperService
   */
  protected NrgiParagraphButtonLinkHelperService $paragraphButtonLinkHelperService;

  /**
   * Paragraph instance.
   *
   * @var \Drupal\paragraphs\ParagraphInterface
   */
  protected ParagraphInterface $paragraph;

  /**
   * View mode to use for rendering featured pages.
   *
   * @var string
   */
  protected string $viewMode;

  /**
   * The show image toggle field name.
   *
   * @var string
   */
  protected string $imageToggleField = 'field_show_image';

  /**
   * The show background toggle field name.
   *
   * @var string
   */
  protected string $backgroundToggleField = 'field_show_background';

  /**
   * The items per row field name.
   *
   * @var string
   */
  protected string $itemsPerRowField = 'field_layout';

  /**
   * The page content items field name.
   *
   * @var string
   */
  protected string $contentField = 'field_content';

  /**
   * The link field name.
   *
   * @var string
   */
  protected string $linkField = 'field_link';

  /**
   * Whether to render images on featured pages.
   *
   * @var bool
   */
  protected bool $withImage = FALSE;

  /**
   * Constructs a new NrgiFeaturedPageService object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer,
    NrgiParagraphButtonLinkHelperService $paragraph_button_link_helper_service
  ) {

    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->paragraphButtonLinkHelperService = $paragraph_button_link_helper_service;
  }

  /**
   * Set image toggle field name.
   *
   * @param string $image_toggle_field
   *   The image toggle field name.
   */
  public function setImageToggleField(string $image_toggle_field): void {
    $this->imageToggleField = $image_toggle_field;
  }

  /**
   * Set background toggle field name.
   *
   * @param string $background_toggle_field
   *   Background toggle field name.
   */
  public function setBackgroundToggleField(string $background_toggle_field): void {
    $this->backgroundToggleField = $background_toggle_field;
  }

  /**
   * Set items per row field name.
   *
   * @param string $items_per_row_field
   *   The items per row field name.
   */
  public function setItemsPerRowField(string $items_per_row_field): void {
    $this->itemsPerRowField = $items_per_row_field;
  }

  /**
   * Set link field name.
   *
   * @param string $link_field
   *   Link field name.
   */
  public function setLinkField(string $link_field): void {
    $this->linkField = $link_field;
  }

  /**
   * Set page content items field name.
   *
   * @param string $content_field
   *   Page content items field name.
   */
  public function setContentField(string $content_field): void {
    $this->contentField = $content_field;
  }

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
    $this->paragraph = $variables['paragraph'];

    if (!empty($this->getPageIds()) && $page_nids = $this->getPageIds()) {
      $this->setViewMode();
      // Get node storage instance.
      $storage = $this->entityTypeManager->getStorage('node');
      $page_nodes = $storage->loadMultiple($page_nids);

      $variables['featured_pages'] = [
        'items' => $this->renderFeaturedPages($page_nodes),
        'with_image' => $this->withImage,
        'view_mode' => $this->viewMode,
        'layout' => $this->paragraph->get($this->itemsPerRowField)->getString(),
        'cta' => $this->paragraph->hasField($this->linkField) ? $this->paragraphButtonLinkHelperService->getLinkFieldValues($this->paragraph, $this->linkField) : '',
      ];
    }
  }

  /**
   * Set view mode to use for rendering featured pages.
   */
  protected function setViewMode(): void {
    $this->viewMode = 'featured_page';

    if ($this->paragraph->hasField($this->imageToggleField)) {
      $this->withImage =
        $this->paragraph->get($this->imageToggleField)->value === '1';
    }

    if ($this->withImage) {
      $this->viewMode .= '_with_image';
    }

    if ($this->paragraph->hasField($this->itemsPerRowField)
        && $layout = $this->paragraph->get(
        $this->itemsPerRowField)->getString()) {
      $this->viewMode .= '_' . $layout . '_per_row';
    }
    else {
      $this->viewMode .= '_4_per_row';
    }
  }

  /**
   * Get array of selected pages node ids.
   *
   * @return array
   *   The array of page node ids.
   */
  protected function getPageIds(): array {
    $page_nids = [];

    if ($this->paragraph->hasField($this->contentField)
        && $page_items = $this->paragraph->get($this->contentField)) {

      foreach ($page_items as $page_item) {
        $page_nids[] = $page_item->get('entity')->getTargetIdentifier();
      }

    }

    return $page_nids;
  }

  /**
   * Render featured cards.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $page_nodes
   *   Array of page nodes.
   *
   * @return array
   *   The array of page node render arrays.
   *
   * @throws \Exception
   */
  protected function renderFeaturedPages(array $page_nodes): array {
    $render_arrays = [];

    if ($page_nodes) {
      $view_builder = $this->entityTypeManager->getViewBuilder('node');
      foreach ($page_nodes as $page_node) {
        // Build the view object and render the page node.
        $view = $view_builder->view($page_node, $this->viewMode);
        $render_arrays[] = $this->renderer->render($view);
      }
    }
    return $render_arrays;
  }

}
