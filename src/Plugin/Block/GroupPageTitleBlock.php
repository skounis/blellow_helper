<?php

declare(strict_types = 1);

namespace Drupal\blellow_helper\Plugin\Block;

use Drupal\Core\Access\AccessResult;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
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
    return AccessResult::allowedIf(!empty($subtitle));
  }

  /**
   * Select the proper subtitle for the page.
   * When we are within the context of a group its name is used for the title.
   * The title of the actual node/page is displayed as a subtitle.
   * @return String|null
   *   The subtitle.
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getSubtitle() {
    $metadata = $this->getContext('page_header')->getContextData()->getValue();
    $subtitle = $metadata['subtitle'] ?? $this->fallback($metadata);
    return $subtitle;
  }

  /**
   * If there is no subtitle try to fall back in page title
   *
   * @param $metadata
   *   The metadata array.
   * @return String|null
   *   The subtitle.
   */
  private function fallback($metadata) {
    if (!array_key_exists ( 'title', $metadata )) {
      return null;
    }
    if ($metadata['title'] == $metadata['page_title']) {
      return null;
    }
    return $metadata['page_title'];
  }

}
