<?php

namespace Drupal\project_backend\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Social Media Share Block.
 *
 * @Block(
 *   id = "social_media_share_block",
 *   admin_label = @Translation("Social Media Share block"),
 *   category = @Translation("Social Media Share"),
 * )
 */
class SocialMediaShareBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $pathMatcher;

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * @var \Drupal\Core\Http\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs an LanguageBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\CurrentPathStack $pathCurrent
   *   The current path.
   * @param \Drupal\Core\Http\RequestStack $requestStack
   *   The request stack service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   The current route.
   */
  public function __construct(
    array $configuration, $plugin_id, $plugin_definition,
    PathMatcher $pathMatcher,
    CurrentRouteMatch $routeMatch,
    RequestStack $requestStack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->pathMatcher = $pathMatcher;
    $this->routeMatch = $routeMatch;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.matcher'),
      $container->get('current_route_match'),
      $container->get('request_stack')
    );
  }

  const PARAMETERS = [
    'linkedin' => [
      'parameters' => [
        'url' => 'url',
        'title' => 'title',
        'summary' => 'summary',
      ],
      'social_media' => 'https://www.LinkedIn.com/shareArticle?mini=true&'
    ],
    'twitter' => [
      'parameters' => [
        'url' => 'text',
        'title' => 'title',
        'summary' => 'summary',
        ],
      'social_media' => 'https://twitter.com/intent/tweet?',
    ],
    'facebook' => [
      'parameters' => [
        'url' => 'u',
        'title' => 'title',
        'summary' => 'summary',
      ],
      'social_media' => 'https://www.facebook.com/sharer/sharer.php?',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function build() {
    // get the node title, description and url
    $request = $this->requestStack->getCurrentRequest();
    $url = $request->getSchemeAndHttpHost() . $request->getRequestUri();
    $node = $this->routeMatch->getParameter('node');
    if (!$node) {
      return [];
    }
    $title = $node->getTitle();
    $description = '';
    if ($node->hasField('field_short_description')) {
      $description = $node->field_short_description->value;
    }
    if ($node->hasField('field_intro_text')) {
      $description = $node->field_intro_text->value;
    }
    // for homepage the title is the url
    if ($this->pathMatcher->isFrontPage()) {
      $title = $url;
    }

    // build url for every social network
    $social_network_share = [];
    foreach (self::PARAMETERS as $social_network => $parameters) {
      $social_network_share[$social_network] = $parameters['social_media']
        . http_build_query([$parameters['parameters']['url'] => $url,
          $parameters['parameters']['title'] => $title,
          $parameters['parameters']['summary'] ??
          $parameters['parameters']['summary'] = 'summary' => $description]);
    }

    // add data for email_subject and email_body
    $social_network_share['email_subject'] = $title;
    $social_network_share['email_body'] = $url;

    return $social_network_share;

  }
}
