<?php

namespace Drupal\nrgi_frontend;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\nrgi_frontend\Services\NrgiParagraphButtonLinkHelperService;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Class NrgiFeaturedCardsBase - base class for NRGI different featured cards.
 */
class NrgiFeaturedCardsBase {

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
   * @var \Drupal\nrgi_frontend\Services\NrgiParagraphButtonLinkHelperService
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
   * Set paragraph.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph.
   */
  public function setParagraph(ParagraphInterface $paragraph): void {
    $this->paragraph = $paragraph;
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
   * Set view mode to use for rendering featured pages.
   *
   * @param string $base_view_mode
   *   The base view mode.
   */
  protected function setViewMode(string $base_view_mode): void {
    $this->viewMode = $base_view_mode;

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
   * Get array of selected node ids.
   *
   * @return array
   *   The array of page node ids.
   */
  protected function getNodeIds(): array {
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
   * @param \Drupal\Core\Entity\EntityInterface[] $nodes
   *   Array of nodes.
   *
   * @return array
   *   The array of page node render arrays.
   *
   * @throws \Exception
   */
  protected function renderFeaturedCards(array $nodes): array {
    $render_arrays = [];

    if ($nodes) {
      $view_builder = $this->entityTypeManager->getViewBuilder('node');
      foreach ($nodes as $node) {
        // Build the view object and render the page node.
        $view = $view_builder->view($node, $this->viewMode);
        $render_arrays[] = $this->renderer->render($view);
      }
    }
    return $render_arrays;
  }

}
