<?php

namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata\Resolver;

use Drupal\blellow_helper\Plugin\PageHeaderMetadata\Model\FutCurrentEntities;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

/**
 * Class NotitleMetadataResolverFactory
 *
 * @package Drupal\blellow_helper\Plugin\PageHeaderMetadata
 */
class NotitleMetadataResolverFactory {


  public static function create(FutCurrentEntities $entities): MetadataResolverBase {

    $entity = $entities->getPrimary();
    // TODO: Handle routes with no entity
    $className = $entity ? get_class($entity) : 'NULL';

    $instance = NULL;

    switch ($className) {
      case Group::class:
        $instance = new GroupMetadataResolver($entities);
        break;
      default:
        $instance = new NotitleMetadataResolver($entities);
    }
    return $instance;
  }

}