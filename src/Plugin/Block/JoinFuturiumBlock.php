<?php

declare(strict_types = 1);

namespace Drupal\blellow_helper\Plugin\Block;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the login widget as a block.
 *
 * @Block(
 *   id = "cnect_join_futurium",
 *   admin_label = @Translation("Join Futurium block"),
 *   category = @Translation("Cnect Corporate blocks"),
 * )
 */
class JoinFuturiumBlock extends BlockBase  implements ContainerFactoryPluginInterface{

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Construct the footer block object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cache = new CacheableMetadata();
    $cache->addCacheContexts(['languages:language_interface']);
    $config = $this->configFactory->get('blellow_helper.data.welcome');

    $cache->addCacheableDependency($config);
    $build['#theme'] = 'cnect_corporate_blocks_join_futurium';

    $build['#attached'] = [
      'library' => [
        'blellow_helper/join_futurium',
      ],
    ];

    NestedArray::setValue($build, ['#corporate_welcome', 'join', 'title'], $config->get('join_title'));
    NestedArray::setValue($build, ['#corporate_welcome', 'join', 'body'], $config->get('join_body'));
    NestedArray::setValue($build, ['#corporate_welcome', 'join', 'call'], $config->get('join_call'));
    NestedArray::setValue($build, ['#corporate_welcome', 'join', 'target'], $config->get('join_target'));

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->configFactory->get('blellow_helper.data.welcome');

    $form['join_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Header'),
      '#description' => $this->t('The header of block'),
      '#default_value' => $this->getSafeString($config, 'join_title')
    ];

    $form['join_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#description' => $this->t('The body of block'),
      '#default_value' => $this->getSafeString($config, 'join_body')
    ];

    $form['join_call'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('The label of the button.'),
      '#default_value' => $this->getSafeString($config, 'join_call')
    ];

    $form['join_target'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Target'),
      '#description' => $this->t('The target URL or relative path.'),
      '#default_value' => $this->getSafeString($config, 'join_target')
    ];

    return $form;
  }

  /**
   * Resolves a value for the configuration
   * @param $config
   *   The configuration
   * @param $key
   *   The key
   * @return string
   *   Returns the value or an empty string
   */
  private function getSafeString($config, $key) {
    return isset($config->getRawData()[$key]) ? $config->getRawData()[$key]: '';
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $values = $form_state->getValues();
    $config = $this->configFactory->getEditable('blellow_helper.data.welcome');

    foreach ($values as $key => $value) {
      if(array_key_exists($key, $config->getRawData())) {
        $config->set($key, $value);
      }
    }
    $config->save();
  }
}
