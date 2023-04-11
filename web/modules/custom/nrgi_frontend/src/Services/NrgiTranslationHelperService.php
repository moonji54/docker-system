<?php

namespace Drupal\nrgi_frontend\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\node\NodeInterface;

/**
 * Helper methods for asymmetrical/symmetrical Translation.
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
    EntityTypeManagerInterface $entity_type_manager
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
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
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
    else {
      // Symmetrical - Drupal default node translation.
      $languages = $node->getTranslationLanguages();

      foreach ($languages as $language) {
        if ($language->getId() != $node->language()->getId()) {
          $links[] = [
            'title' => $language->getName(),
            'url' => $node->getTranslation($language->getId())
              ->toUrl()
              ->toString(),
          ];
        }
      }
    }

    return $links;
  }

  /**
   * Get the available (symmetrical) translations string for node cards.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return string|void
   *   The generated string, empty if no translations.
   */
  public function getCardAvailableLanguagesString(NodeInterface $node) {
    $available_translations_string = '';
    $languages = $node->getTranslationLanguages();
    $available_langcodes = count(array_keys($languages)) > 1 ? array_keys($languages) : [];

    if ($available_langcodes) {
      $available_translations_string .= t('Also in');
      $i = 0;
      foreach ($available_langcodes as $available_langcode) {
        if ($i > 0) {
          $available_translations_string .= ',';
        }
        if ($available_langcode != $node->language()->getId()) {
          $available_translations_string .= ' ' . $available_langcode;
          $i++;
        }
      }
    }
    return $available_translations_string;
  }

}
