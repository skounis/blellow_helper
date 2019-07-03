<?php

declare(strict_types = 1);

namespace Drupal\blellow_helper\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Entity\GroupInterface;
use False\MyClass;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Page title' block.
 *
 * @Block(
 *   id = "blellow_helper_group_page_title",
 *   admin_label = @Translation("Page title"),
 *   category = @Translation("Cnect Corporate blocks"),
 *   context = {
 *     "page_header" = @ContextDefinition("map", label = @Translation("Page header metadata"))
 *   }
 * )
 */
class GroupPageTitleBlock extends BlockBase implements ContainerFactoryPluginInterface, TitleBlockPluginInterface, ContextAwarePluginInterface {

  use StringTranslationTrait;


  /**
   * Stores the configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The page title: a string (plain title) or a render array (formatted title).
   *
   * @var string|array
   */
  protected $title = '';

  /**
   * Constructs a new PageHeaderTitleBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, RouteMatchInterface $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configFactory = $config_factory;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $title = $this->getSubtitle();
    $build = [
      '#attached' => [
        'library' => [
          'blellow_helper/group_page_title',
        ]
      ],
      '#theme' => 'group_page_title_block',
      '#title' => $title
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title): self {
    $this->title = $title;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $subtitle = $this->getSubtitle();
    return \Drupal\Core\Access\AccessResult::allowedIf(!empty($subtitle));
  }

  private function getSubtitle() {
    $metadata = $this->getContext('page_header')->getContextData()->getValue();
    $subtitle = $metadata['subtitle'] ?? NULL;
    return $subtitle;
  }

}