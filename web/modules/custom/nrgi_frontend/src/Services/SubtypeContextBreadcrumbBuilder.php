<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkManager;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Build breadcrumbs based on active trail from context.
 */
class SubtypeContextBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Helper class determining whether the route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected AdminContext $routerAdminContext;

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManager
   */
  protected MenuLinkManager $menuLinkManager;

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected CurrentPathStack $currentPathService;

  /**
   * Constructs a new SubtypeContextBreadcrumbBuilder object.
   *
   * @param \Drupal\Core\Routing\AdminContext $router_admin_context_service
   *   The service router admin context service.
   * @param \Drupal\Core\Menu\MenuLinkManager $menu_link_manager
   *   The menu link manager.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path_service
   *   The current path service.
   */
  public function __construct(
    AdminContext $router_admin_context_service,
    MenuLinkManager $menu_link_manager,
    CurrentPathStack $current_path_service
  ) {
    $this->routerAdminContext = $router_admin_context_service;
    $this->menuLinkManager = $menu_link_manager;
    $this->currentPathService = $current_path_service;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    $route = \Drupal::routeMatch()->getRouteObject();
    $is_admin = FALSE;

    if (!empty($route)) {
      $is_admin_route = $this->routerAdminContext
        ->isAdminRoute($route);
      $has_node_operation_option = $route->getOption('_node_operation_route');
      $is_admin = ($is_admin_route || $has_node_operation_option);
    }
    else {
      $current_path = $this->currentPathService->getPath();
      if (preg_match('/node\/(\d+)\/edit/', $current_path, $matches)) {
        $is_admin = TRUE;
      }
      elseif (preg_match('/taxonomy\/term\/(\d+)\/edit/', $current_path, $matches)) {
        $is_admin = TRUE;
      }
    }
    return !$is_admin;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match): Breadcrumb {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['url.path']);

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {

      // Start with home page.
      $breadcrumb->addLink(
        Link::createFromRoute($this->t('Home'), '<front>')
      );

      /** @var \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager */
      $menu_link_manager = $this->menuLinkManager;

      // Get the Node ID referenced by this subtype.
      // Default to the current node, in case this is already something
      // that is in the menu.
      $route_node_id = $node->id();
      if ($subtype_parent_node = $this->getSubtypeParent($node)) {
        $route_node_id = $subtype_parent_node->id();
      }
      $menu_links = $menu_link_manager->loadLinksByRoute('entity.node.canonical', [
        'node' => $route_node_id,
      ]);

      // Get the Menu Link Content IDs for that Node.
      if (is_array($menu_links) && count($menu_links)) {
        /** @var \Drupal\menu_link_content\Plugin\Menu\MenuLinkContent $menu_link */
        $menu_link = reset($menu_links);
        $menu_link_ids = array_keys($menu_links);
        $menu_link_ids = [reset($menu_link_ids)];
        if ($menu_link->getParent()) {
          $parents = $menu_link_manager->getParentIds($menu_link->getParent());
          $menu_link_ids = array_merge($menu_link_ids, $parents);
        }
      }

      // Add the menu links as Breadcrumbs.
      if (isset($menu_link_ids)) {
        foreach (array_reverse($menu_link_ids) as $link_id) {
          $link = $menu_link_manager->getInstance(['id' => $link_id]);
          $breadcrumb->addLink(
            Link::fromTextAndUrl($link->getTitle(), $link->getUrlObject())
          );
        }
      }
    }
    return $breadcrumb;
  }

  /**
   * Get the subtype parent node if it exists.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The current node.
   *
   * @return \Drupal\node\NodeInterface|bool
   *   The subtype parent or false.
   */
  protected function getSubtypeParent(NodeInterface $node): NodeInterface | bool {

    if ($node->hasField('field_resource_type') &&
        !$node->get('field_resource_type')->isEmpty()) {

      // Subtype term related to this node.
      $subtype_term = $node->field_resource_type->entity;

      if (
        $subtype_term instanceof TermInterface
        && $subtype_term->hasField('field_parent_node')
        && !$subtype_term->get('field_parent_node')->isEmpty()
      ) {

        // Parent node reference from the subtype term.
        $subtype_parent_node = $subtype_term->field_parent_node->entity;
        if ($subtype_parent_node instanceof NodeInterface) {
          return $subtype_parent_node;
        }
      }
    }
    return FALSE;
  }

}
