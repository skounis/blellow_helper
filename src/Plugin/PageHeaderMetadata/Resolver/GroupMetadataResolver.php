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
    $node = $this->entities->getRelated();
    $visual_identity = FutGroupHelper::getVisualIdentity($group);
    $group_operations = FutGroupHelper::getGroupOperations($group);
    $subtitle = isset($node) ? $node->label() : NULL;
    $groupUrl = $group->toUrl('canonical', [
      'absolute' => TRUE,
      'language' => \Drupal::languageManager()->getCurrentLanguage(),
    ]);
    $url = [];
    if (!empty($groupUrl)) {
      $url = $groupUrl->toString();
    }

    $actions = FutGroupHelper::groupActionsToECLArray($group_operations);
    $metadata = [
      'subtitle' => $subtitle,
      'image' => $visual_identity,
      'actions' => $actions,
      'url' => $url,
      '_group' => $group,
      '_node' => $node,
      '_extra' => GroupMetadataResolver::class
    ];

    return $metadata;
  }
}
