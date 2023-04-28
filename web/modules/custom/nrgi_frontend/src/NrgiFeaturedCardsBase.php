<?php

namespace Drupal\nrgi_frontend;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\NodeInterface;
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
   * The manually selected content items field name.
   *
   * @var string
   */
  protected string $contentField = 'field_content';

  /**
   * The manually selected exclustions.
   *
   * @var string
   */
  protected string $manualExlcusionsField = 'field_exclusions';

  /**
   * The link field name.
   *
   * @var string
   */
  protected string $linkField = 'field_link';

  /**
   * Field name used for page builder components.
   *
   * @var string
   */
  protected string $pageBuilderField = 'field_page_builder';

  /**
   * The field name to indicated allowed node types in automated contents.
   *
   * @var string
   */
  protected string $typesField = 'field_types';

  /**
   * The fields to indicated allowed taxonomy filters in automated contents.
   *
   * @var array
   */
  protected array $taxonomyFields;

  /**
   * The field name to indicate the limit in automated contents.
   *
   * @var string
   */
  protected string $quantityField = 'field_quantity';

  /**
   * The field names to indicate the sorting in automated contents.
   *
   * @var array
   */
  protected array $sortFields;

  /**
   * The field name to indicate the sorting order in automated contents.
   *
   * @var string
   */
  protected $sortOrder;

  /**
   * Set if date filter should be applied i.e. upcoming/past/both.
   *
   * @var string
   */
  protected string $dateFilter;

  /**
   * The date fields to check the content's recency by.
   *
   * @var array
   */
  protected array $dateFields;


  /**
   * Whether to render images on featured cards.
   *
   * @var bool
   */
  protected bool $withImage = FALSE;

  /**
   * Allowed types for automated nodes.
   *
   * @var array
   */
  protected array $types;

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
   * Set page builder field name.
   *
   * @param string $page_builder_field
   *   The page builder field name.
   */
  public function setPageBuilderFieldName(string $page_builder_field): void {
    $this->pageBuilderField = $page_builder_field;
  }

  /**
   * Set manual exclusions field name.
   *
   * @param string $manual_exclusions_field
   *   The manual exclusions field name.
   */
  public function setManualExclusionsFieldName(string $manual_exclusions_field): void {
    $this->manualExlcusionsField = $manual_exclusions_field;
  }

  /**
   * Set allowed types.
   *
   * @param array $types
   *   The node types.
   */
  public function setAllowedTypes(array $types): void {

    $this->types = [];

    if ($this->typesField && $this->paragraph->hasField($this->typesField)
        && $this->paragraph->get($this->typesField)
        && $setting_types = $this->paragraph->get($this->typesField)
          ->getValue()) {
      foreach ($setting_types as $type) {
        $this->types[] = $type['value'];
      }
    }
    elseif ($types) {
      // Default to provided values if not set by the editor.
      $this->types = $types;
    }
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
   * Set taxonomy conditions field name.
   *
   * @param array $taxonomies_field_names
   *   The taxonomy conditions field names.
   */
  public function setTaxonomyFields(array $taxonomies_field_names): void {
    $this->taxonomyFields = $taxonomies_field_names;
  }

  /**
   * Set quantity field name.
   *
   * @param string $quantity_field_name
   *   The quantity field name.
   */
  public function setQuantity(string $quantity_field_name): void {
    $this->quantityField = $quantity_field_name;
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
   * Set date filter.
   *
   * @param string $date_filter
   *   The date filter.
   */
  public function setDateFilter(string $date_filter): void {
    $this->dateFilter = $date_filter;
  }

  /**
   * Set date field.
   *
   * @param array $date_fields
   *   The date filter.
   */
  public function setDateFields(array $date_fields): void {
    $this->dateFields = $date_fields;
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
    else {
      $this->withImage = TRUE;
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
      $this->viewMode .= '_3_per_row';
    }
  }

  /**
   * Get array of selected node ids.
   *
   * @return array
   *   The array of node ids.
   */
  protected function getNodeIds(ParagraphInterface $paragraph = NULL): array {
    $nids = [];
    if (!$paragraph) {
      $paragraph = $this->paragraph;
    }

    if ($paragraph->hasField($this->contentField)
        && $node_items = $this->paragraph->get($this->contentField)) {

      foreach ($node_items as $page_item) {
        $nids[] = $page_item->get('entity')->getTargetIdentifier();
      }

    }

    return $nids;
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

  /**
   * Get node nids already selected by the site editor.
   *
   * @return array
   *   An array of NIDs.
   */
  protected function getExclusions(): array {

    $exclusions = $components = [];
    $node = $this->paragraph->getParentEntity();
    if (!$node) {
      return $exclusions;
    }

    if ($node->hasField($this->pageBuilderField)) {
      $components = $node->get($this->pageBuilderField);
    }
    if ($components) {
      $found_current = FALSE;
      foreach ($components as $component) {

        // Only consider items before self as pre-selections.
        if ($component->entity->id() == $this->paragraph->id()) {
          $found_current = TRUE;
        }

        // If the bundle is featured cards, get the selections.
        if (!$found_current && $component->entity->bundle() == $this->paragraph->bundle()) {
          $exclusions = $this->getPreSelectionsFromFeaturedCardsParagraph($exclusions,
            $component->entity);
        }
      }
    }

    // Add self.
    return $this->getPreSelectionsFromFeaturedCardsParagraph($exclusions, $this->paragraph);
  }

  /**
   * Get pre-selections from featured cards paragraph.
   *
   * @param array $exclusions
   *   Exclusions array.
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   A paragraph.
   *
   * @return array
   *   Exclusions merged with pre selections on the same node.
   */
  protected function getPreSelectionsFromFeaturedCardsParagraph(
    array $exclusions,
    ParagraphInterface $paragraph,
  ): array {

    $selections = $this->getNodeIds($paragraph);
    if ($selections) {
      $exclusions = array_merge($exclusions, $selections);
    }

    return $exclusions;
  }

  /**
   * Get the manually selected exclusions.
   *
   * @return array
   *   The manual exclusions node ids.
   */
  protected function getManualExclusions(): array {
    $manual_exclusions_nids = [];

    if ($this->paragraph->hasField($this->manualExlcusionsField)
        && $exclusions = $this->paragraph->get($this->manualExlcusionsField)) {
      foreach ($exclusions as $exclusion) {
        if ($exclusion->entity instanceof NodeInterface) {
          $manual_exclusions_nids[] = $exclusion->entity->id();
        }
      }
    }

    return $manual_exclusions_nids;
  }

}
