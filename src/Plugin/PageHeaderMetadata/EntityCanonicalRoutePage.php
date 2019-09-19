<?php

declare(strict_types = 1);

namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\fut_group\RequestEntityExtractor;
use Drupal\blellow_helper\PageHeaderMetadataPluginBase;
use Drupal\blellow_helper\Plugin\PageHeaderMetadata\Model\FutCurrentEntities;
use Drupal\blellow_helper\Plugin\PageHeaderMetadata\Resolver\MetadataResolverFactory;
use Drupal\group\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a page header metadata plugin that extracts data from current entity.
 *
 * @PageHeaderMetadata(
 *   id = "entity_canonical_route",
 *   label = @Translation("Default entity metadata extractor")
 * )
 */
class EntityCanonicalRoutePage extends PageHeaderMetadataPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The request entity extractor. Gets access to the active or related Group
   * in the context.
   *
   * @var \Drupal\fut_group\RequestEntityExtractor
   */
  protected $requestEntityExtractor;

  /**
   * Creates a new EntityPageHeaderMetadata object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   * @param \Drupal\fut_group\RequestEntityExtractor $request_entity_extractor
   *   The request entity extractor.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, RouteMatchInterface $current_route_match, RequestEntityExtractor $request_entity_extractor) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->currentRouteMatch = $current_route_match;
    $this->requestEntityExtractor = $request_entity_extractor;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('fut_group.request_entity_extractor')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $entity = $this->getEntityFromCurrentRoute();

    return !empty($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $entities = $this->getEntities();

    // Create a resolver
    $resolver = MetadataResolverFactory::create($entities);
    $metadata = $resolver->getMetadata();

    $cacheability = new CacheableMetadata();
    $cacheability
      ->addCacheableDependency($entities->getPrimary())
      ->addCacheContexts(['route'])
      ->applyTo($metadata);

    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromCurrentRoute(): ?ContentEntityInterface {

    /*
     * Routes with a group entity
     * If everything else fails use names to match a route with a Group
     * e.g:
       $group_routes = array(
        "view.fut_group_library.page_group_library",
        "view.fut_group_posts.page_group_posts",
        "view.fut_group_events.page_group_events",
        "view.group_members.page_1",
        "view.group_nodes.page_1",
        "view.group_pending_members.page_1",
        "view.subgroups.page",
        "fut_group.manage_group",
        "fut_group.manage_group.edit",
        "group_permissions.override_group_permissions",
        "fut_group.manage_group.navigation",
        "fut_group.manage_group.members",
        "fut_group.manage_group.member_requests",
        "view.group_invitations.page_1",
        "fut_group.manage_group.layout",
        "fut_group.manage_group.privacy",
        "fut_group.manage_group_content.posts");
     *
     */
    $group_routes = array();

    if (($route = $this->currentRouteMatch->getRouteObject()) && ($parameters = $route->getOption('parameters'))) {
      // Determine if the current route represents an entity.
      foreach ($parameters as $name => $options) {
        if (isset($options['type']) && strpos($options['type'], 'entity:') === 0) {
          $entity = $this->currentRouteMatch->getParameter($name);
          if ($entity instanceof ContentEntityInterface && ($this->currentRouteMatch->getRouteName() === "entity.{$entity->getEntityTypeId()}.canonical") ||
            $entity instanceof Group ||
            in_array($this->currentRouteMatch->getRouteName(), $group_routes))  {
            return $entity;
          }
        }
      }
    }

    return NULL;
  }

  /**
   * @return \Drupal\blellow_helper\Plugin\PageHeaderMetadata\Model\FutCurrentEntities
   */
  public function getEntities(): FutCurrentEntities {
    // Currently we care only for Groups and Nodes. If both exist
    $arr = array();
    $arr [] = $this->requestEntityExtractor->getGroup();
    $arr [] = $this->requestEntityExtractor->getNode();
    $arr = array_filter($arr);

    // If Group Extractor fails try to capture the entity from the route
    // TODO: Fix the reducers in `FutCurrentEntities` when
    if (empty($arr)) {
      $arr [] = $this->getEntityFromCurrentRoute();
    }

    $entities = new FutCurrentEntities($arr);

    return $entities;
  }
}
