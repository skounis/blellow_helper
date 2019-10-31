<?php

namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata\Resolver;

/**
 * Class NodeMetadataResolver
 *
 * @package Drupal\blellow_helper\Plugin\PageHeaderMetadata
 */
class NotitleMetadataResolver extends MetadataResolverBase {

  public function _getMetadata() {
    $metadata = [
      'title' => ''
    ];

    return $metadata;
  }
}