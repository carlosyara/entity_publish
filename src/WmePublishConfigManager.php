<?php

namespace Drupal\wme_publish;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;

/**
 * Class WmePublishConfigManager.
 */
class WmePublishConfigManager {

  /**
   * Drupal\Core\Config\ImmutableConfig definition.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new WmePublishConfigManager object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('wme_publish.settings');
  }

  /**
   * GetEntityTypes.
   *
   * @return array
   */
  public function getEntityTypes() {
    $config_bundles = $this->config->get('enable_bundles');
    return $config_bundles ? array_keys($config_bundles) : [];
  }

  /**
   * GetPublishConfig.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   */
  public function getPublishConfig() : ImmutableConfig {
    return $this->config;
  }

  /**
   * Return enable bundles by entity type.
   *
   * @param  string $entity_type
   * @return array
   */
  public function getEnableBundles($entity_type) : array {
    $config_bundles = $this->config->get('enable_bundles');
    return isset($config_bundles[$entity_type]) ? $config_bundles[$entity_type] : [];
  }

}
