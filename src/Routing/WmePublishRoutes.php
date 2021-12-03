<?php

namespace Drupal\wme_publish\Routing;

use Drupal\wme_publish\WmePublishConfigManager;
use Symfony\Component\Routing\Route;

/**
 * Class WmePublishRoutes.
 *
 * Generate Publish routes.
 */
class WmePublishRoutes {

  /**
   * The base plugin ID.
   *
   * @var \Drupal\wme_publish\WmePublishConfigManager
   */
  protected $publishConfigManager;

  /**
   * Constructs a new WmePublishRoutes object.
   */
  public function __construct(WmePublishConfigManager $publish_config_manager) {
    $this->publishConfigManager = $publish_config_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $entity_types = $this->publishConfigManager->getEntityTypes();
    $routes = [];
    foreach ($entity_types as $entity_type) {
      $publish_route = "wme_entity.$entity_type.publish";
      $routes[$publish_route] = new Route(
        '/' . $entity_type . '/{' . $entity_type . '}/wme_publish',
        [
          '_form' => '\Drupal\wme_publish\Form\PublishForm',
          '_title_callback' => 'wme_publish.title:getTitle',
          'entity_type' => $entity_type,
          'operation' => 'publish',
        ],
        [
          '_custom_access' => 'wme_publish.access:access',
        ],
        [
          'parameters' => [
            $entity_type => [
              'type' => "entity:$entity_type",
            ],
          ],
        ]
      );

      $unpublish_route = "wme_entity.$entity_type.unpublish";
      $routes[$unpublish_route] = new Route(
        '/' . $entity_type . '/{' . $entity_type . '}/wme_unpublish',
        [
          '_form' => '\Drupal\wme_publish\Form\PublishForm',
          '_title_callback' => 'wme_publish.title:getTitle',
          'entity_type' => $entity_type,
          'operation' => 'unpublish',
        ],
        [
          '_custom_access' => 'wme_publish.access:access',
        ],
        [
          'parameters' => [
            $entity_type => [
              'type' => "entity:$entity_type",
            ],
          ],
        ]
      );

    }
    return $routes;
  }

}
