<?php

namespace Drupal\wme_publish\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\wme_publish\WmePublishConfigManager;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generate Local Tasks for Publish form.
 */
class WmePublishLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The base plugin ID.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The base plugin ID.
   *
   * @var \Drupal\wme_publish\WmePublishConfigManager
   */
  protected $publishConfigManager;

  /**
   * Constructs a new ConfigTranslationLocalTasks.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\wme_publish\WmePublishConfigManager $publish_config_manager
   *   The factory for configuration objects.
   */
  public function __construct($base_plugin_id, WmePublishConfigManager $publish_config_manager) {
    $this->basePluginId = $base_plugin_id;
    $this->publishConfigManager = $publish_config_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('wme_publish.config_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $entity_types = $this->publishConfigManager->getEntityTypes();

    foreach ($entity_types as $entity_type) {
      $entity_definition = \Drupal::entityTypeManager()->getDefinition($entity_type);

      $publish_route = "wme_entity.$entity_type.publish";
      $this->derivatives[$publish_route] = $base_plugin_definition;
      $this->derivatives[$publish_route]['title'] = "Publish";
      $this->derivatives[$publish_route]['route_name'] = $publish_route;
      $this->derivatives[$publish_route]['base_route'] = "entity.$entity_type.canonical";
      $this->derivatives[$publish_route]['weight'] = "10";
      $unpublish_route = "wme_entity.$entity_type.unpublish";
      $this->derivatives[$unpublish_route] = $base_plugin_definition;
      $this->derivatives[$unpublish_route]['title'] = "Unpublish";
      $this->derivatives[$unpublish_route]['route_name'] = $unpublish_route;
      $this->derivatives[$unpublish_route]['base_route'] = "entity.$entity_type.canonical";
      $this->derivatives[$unpublish_route]['weight'] = "10";
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
