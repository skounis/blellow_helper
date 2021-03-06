<?php


namespace Drupal\blellow_helper\Plugin\PageHeaderMetadata\Resolver;

use Drupal\blellow_helper\Plugin\PageHeaderMetadata\Model\FutCurrentEntities;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Class MetadataResolverBase
 *
 * Base implementation of the metadata resolver. Creates the metadata array for
 * an entity.
 * @package Drupal\blellow_helper\Plugin\PageHeaderMetadata
 */
abstract class MetadataResolverBase {

  protected $entities;

  /**
   * MetadataResolverBase constructor.
   *
   * @param \Drupal\blellow_helper\Plugin\PageHeaderMetadata\Model\FutCurrentEntities $entities
   */
  function __construct(FutCurrentEntities $entities) {
    $this->entities = $entities;
  }

  /**
   * Resolves and returns the array with the available metadata from the entity
   * @return mixed
   */
  public function getMetadata() {
    //
    // From https://v1--europa-component-library.netlify.com/ec/components/detail/ec-component-page-header--default
    //
    // {#
    //  Parameters:
    //  - "variant" (string) (default: 'default'): could be "basic" or "default"
    //  - "identity" (string) (default: ''): name of the site, i.e. "Site identity"
    //  - "breadcrumb" (array)  (default: []): collection of @ecl/ec-component-link
    //  - "language_switcher" (object) (default: ''): object of type
    //                                                @ecl/generic-component-lang-select-page
    //  - "title" (string) (default: ''): page title
    //  - "introduction" (string) (default: ''): A short and striking phrase
    //                                           related to the site identification
    //                                           (45 characters maximum)
    //  - "metas" (array) (default: []): array of strings ; each one corresponds
    //                                   to a meta item
    //  - "extra_classes" (string) (default: '')
    //  - "extra_attributes" (array) (default: []): format: [
    //       {
    //         "name" (string) (default: ''),
    //         "value" (string) (default: '')
    //       },
    //     ...
    //     ]
    // #}
    //
    // With the additions:
    //  1. Action Groups (links) rendered as a drop-down button. eg: Group Operations
    //  2. Target link, a url the title could point to. eg: the Group URL
    //
    // Note
    // ====
    // Identity: We will let the Block plugin contribute it in the rendering
    //           array, due to the dependencies it has.
    // Breadcrumb: We will let the Block plugin contribute it in the rendering
    //             array, due to the dependencies it has.
    //
    // Both of the above are common for the site and not related to the
    // resolving process which is based on the typ

    $metadata = [
      'title' => $this->entities->getPrimary()->label(),
      'page_title' => $this->getPageTitle(),
      'metas' => [], // eg: ['news article', '17 September 2014'],
      '_resolverClass' => get_class($this),
      '_entityClass' => get_class($this->entities->getPrimary()),
    ];

    $extra = $this->_getMetadata();

    return array_merge ($metadata, $extra);
  }

  /**
   * Resolves and returns the actual title of the page.
   * @return string
   *   The page title.
   */
  protected function getPageTitle() {
    $title = '';
    $request = \Drupal::request();
    if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
      $title = \Drupal::service('title_resolver')->getTitle($request, $route);
    }
    $title = $this->reduce($title);
    return $title;
  }

  /**
   * Reduce a title into a string
   * @param $title
   *   The title, string, array or TranslatableMarkup
   * @return string|null
   */
  private function reduce($title) {
    if (is_string ( $title )) {
      return $title;
    }
    if (is_array ( $title ) &&  array_key_exists ('#markup', $title)) {
      return $title['#markup'];
    }
    if (is_a($title, 'Drupal\Component\Render\MarkupInterface')) {
      return $title->__toString();
    }
    return NULL;
  }
  abstract protected function _getMetadata();
}