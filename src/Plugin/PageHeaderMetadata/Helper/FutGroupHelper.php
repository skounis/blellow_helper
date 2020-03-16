<?php


namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata\Helper;


use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\image\Entity\ImageStyle;

class FutGroupHelper {

  /**
   * Provides a list of operations for a group.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   The group to generate the operations for.
   *
   * @return array
   *   An associative array of operation links to show in the block.
   */
  public static function getGroupOperations(GroupInterface $group) {
    $group_operations = [];

    foreach ($group->getGroupType()->getInstalledContentPlugins() as $plugin) {
      /** @var \Drupal\group\Plugin\GroupContentEnablerInterface $plugin */
      $group_operations += $plugin->getGroupOperations($group);
    }

    // Do not keep request membership form and button together. Form wins.
    $hasForm = FutGroupHelper::hasRegistrationForm($group);
    $group_operations = array_filter($group_operations, function($item) use ($hasForm) {
      $route = $item['url']->getRouteName();
      return !($route === 'entity.group.request_membership' && $hasForm);
    });

    if ($group_operations) {
      // Allow modules to alter the collection of gathered links.
      \Drupal::moduleHandler()->alter('group_operations', $group_operations, $group);

      // Sort the operations by weight.
      uasort($group_operations, '\Drupal\Component\Utility\SortArray::sortByWeightElement');
      return $group_operations;
    }
  }

  /**
   * Checks if a group has a request membership form attached.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *
   * @return bool
   */
  public static function hasRegistrationForm(GroupInterface $group) {
    $form = $group->get('fut_registration_form')->getValue();
    return isset($form) && is_array($form) && !empty($form);
  }

  /**
   * Resolves the parent of a Group if any.
   *
   * @param GroupInterface $group
   *  The group to resolve the parent for.
   * @return mixed|null
   *  The parent group or null.
   */
  public static function getParentGroup(GroupInterface $group) {
    if (!$group) {
      return NULL;
    }

    $parent = \Drupal::service('ggroup.group_hierarchy_manager')->getGroupSupergroups($group->id());
    $parent = reset($parent);
    $parent = (!!$parent ? $parent : NULL);
    return $parent;
  }

  /**
   * Constructs array with src path for image and alt text.
   *
   * @param \Drupal\group\Entity\GroupInterface $group
   *   Group Entity.
   *
   * @return array
   *   Array with src and alt.
   */
  public static function getVisualIdentity(GroupInterface $group) {
    if (empty($group->fut_logo->first()->entity)) {
      return '';
    }

    $file_entity = $group->fut_logo->first()->entity;

    $image_src = ImageStyle::load('fut_group_logo')
      ->buildUrl($file_entity
        ->get('uri')
        ->first()
        ->getString());
    $alt = $group->fut_logo->alt ?? '';

    $image = [
      'src' => $image_src,
      'alt' => $alt,
    ];

    return $image;
  }

  /**
   * @param GroupInterface $group
   *  The group to resolve the URL for.
   * @return array|\Drupal\Core\GeneratedUrl|string
   *  The absolut URL to the group.
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public static function getUrl(?GroupInterface $group) {
    $url = [];
    if (!$group) {
      return $url;
    }
    $groupUrl = $group->toUrl('canonical', [
      'absolute' => TRUE,
      'language' => \Drupal::languageManager()->getCurrentLanguage(),
    ]);
    if (!empty($groupUrl)) {
      $url = $groupUrl->toString();
    }
    return $url;
  }

  /**
   * @param GroupInterface $group
   *  The group to resolve the title for.
   * @return string|null
   *  The title or null.
   */
  public static function getTitle(?GroupInterface $group) {
    $title = NULL;
    if (!$group) {
      return $title;
    }
    $title = $group->label();
    return $title;
  }

  /**
   * Converts the group_operations to an ECL friendly array
   *
   * @param $actions
   *  The Group Operations array
   *
   * @return array
   *  An array of [href, label, weight, key]
   */
  public static function groupActionsToECLArray($actions) {
    $actions = $actions ?? [];

    array_walk($actions, function (&$item, $key) {
      $item['key'] = $key;
    });

    $links = array_map(function ($item) {
      return [
        'href' => $item['url'],
        'label' => $item['title'],
        'weight' => $item['weight'],
        'key' => $item['key'],
      ];
    }, $actions);

    $links = array_values($links);

    return $links;
  }
}
