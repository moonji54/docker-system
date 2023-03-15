<?php

namespace Drupal\nrgi_frontend;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\node\NodeInterface;

/**
 * Helper methode for featured people.
 *
 * @package Drupal\ifg_frontend
 */
class NrgiTranslationHelperService {

  /**
   * EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * FeaturedContentPreprocessContentHelperService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
  ) {

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get the language switcher links for a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param bool $asymmetrical
   *   Whether translation is asymmetrical, default to FALSE.
   * @param string $manual_translations_field_name
   *   In case translation is asymmetrical, the field to get the manually
   *   added content items (separate nodes, probably in different language).
   *
   * @return array
   *   The array of language switch links.
   */
  public function getLanguageSwitcherLinks(
    NodeInterface $node,
    bool $asymmetrical = FALSE,
    string $manual_translations_field_name = ''): array {

    $links = [];

    if ($asymmetrical) {
      if ($node->hasField($manual_translations_field_name)
          && $manual_translations_field = $node->get($manual_translations_field_name)) {
        if ($manual_translations_field instanceof EntityReferenceFieldItemListInterface) {
          /** @var \Drupal\Core\Entity\EntityInterface[] $manual_translations_nodes */
          $manual_translations_nodes = $manual_translations_field->referencedEntities();
          foreach ($manual_translations_nodes as $manual_translations_node) {
            $language = $manual_translations_node->language();
            if ($language->getId() !== $node->language()->getId()) {
              $links[] = [
                'title' => $language->getName(),
                'url' => $manual_translations_node->toUrl()->toString(),
              ];
            }
          }

        }
      }
    }
    return $links;
  }

}