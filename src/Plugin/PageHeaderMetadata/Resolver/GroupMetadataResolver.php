<?php

namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata\Resolver;

use Drupal\blellow_helper\Plugin\PageHeaderMetadata\Helper\FutGroupHelper;

/**
 * Class GroupMetadataResolver
 *
 * @package Drupal\blellow_helper\Plugin\PageHeaderMetadata
 */
class GroupMetadataResolver extends MetadataResolverBase {

  public function _getMetadata() {
    $group = $this->entities->getPrimary();
    $parent = FutGroupHelper::getParentGroup($group);
    $node = $this->entities->getRelated();
    $visual_identity = FutGroupHelper::getVisualIdentity($group);
    $group_operations = FutGroupHelper::getGroupOperations($group);
    $subtitle = isset($node) ? $node->label() : NULL;
    $url = FutGroupHelper::getUrl($group);
    $parent_title = FutGroupHelper::getTitle($parent);
    $parent_url = FutGroupHelper::getUrl($parent);
    $actions = FutGroupHelper::groupActionsToECLArray($group_operations);

    $metadata = [
      'subtitle' => $subtitle,
      'image' => $visual_identity,
      'actions' => $actions,
      'url' => $url,
      'parent_title' => $parent_title,
      'parent_url' =>$parent_url,
      '_group' => $group,
      '_parent' => $parent,
      '_node' => $node,
      '_extra' => GroupMetadataResolver::class
    ];

    return $metadata;
  }
}
