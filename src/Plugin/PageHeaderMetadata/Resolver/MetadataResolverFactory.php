<?php

namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata\Resolver;

use Drupal\blellow_helper\Plugin\PageHeaderMetadata\Model\FutCurrentEntities;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

/**
 * Class MetadataResolverFactory
 *
 * @package Drupal\blellow_helper\Plugin\PageHeaderMetadata
 */
class MetadataResolverFactory {


  public static function create(FutCurrentEntities $entities): MetadataResolverBase {

    $entity = $entities->getPrimary();
    // TODO: Handle routes with no entity
    $className = $entity ? get_class($entity) : 'NULL';

    $instance = NULL;

    switch ($className) {
      case Node::class:
        $instance = new NodeMetadataResolver($entities);
        break;
      case Group::class:
        $instance = new GroupMetadataResolver($entities);
        break;

      default:
        $instance = new DefaultMetadataResolver($entities);
    }
    return $instance;
  }

}