<?php

namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata\Resolver;

/**
 * Class NodeMetadataResolver
 *
 * @package Drupal\blellow_helper\Plugin\PageHeaderMetadata
 */
class NodeMetadataResolver extends MetadataResolverBase {

  public function _getMetadata() {
    $metadata = [
      '_extra' => NodeMetadataResolver::class
    ];

    return $metadata;
  }
}