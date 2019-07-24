<?php

declare(strict_types = 1);

namespace Drupal\blellow_helper\Plugin\Block;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an register widget as a block.
 *
 * @Block(
 *   id = "cnect_select_languages",
 *   admin_label = @Translation("Select Langulages block"),
 *   category = @Translation("Cnect Corporate blocks"),
 * )
 */
class SelectLanguagesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'cnect_corporate_blocks_select_languages';

    return $build;
  }

}
