<?php

namespace Drupal\wme_publish;

use Drupal\Core\entity\EntityInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Class PublishAccess.
 */
class PublishAccess {

  /**
   * Drupal\wme_publish\WmePublishConfigManager definition.
   *
   * @var \Drupal\wme_publish\WmePublishConfigManager
   */
  protected $wmePublishConfigManager;

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Drupal\Core\Extension\ModuleHandlerInterface definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\wme_publish\EntityStatus definition.
   *
   * @var \Drupal\wme_publish\EntityStatus
   */
  protected $entityStatus;

  /**
   * Constructs a new PublishAccess object.
   */
  public function __construct(
    WmePublishConfigManager $wme_publish_config_manager,
    CurrentRouteMatch $current_route_match,
    ModuleHandlerInterface $module_handler,
    EntityStatus $entity_status
    ) {
    $this->wmePublishConfigManager = $wme_publish_config_manager;
    $this->currentRouteMatch = $current_route_match;
    $this->moduleHandler = $module_handler;
    $this->entityStatus = $entity_status;
  }

  /**
   * Enable access to publish or unpublish form.
   *
   * @param string $entity_type
   * @param string $operation
   *
   * @return \Drupal\Core\Access\AccessResult
   */
  public function access($entity_type = "", $operation = "") {
    $entity = $this->currentRouteMatch->getParameter($entity_type);
    if ($entity && $this->validateEntityAccess($entity, $operation)) {
      return AccessResult::allowed()
        ->addCacheTags([$entity_type . '_list']);
    }
    else {
      return AccessResult::forbidden()
        ->addCacheTags([$entity_type . '_list']);
    }
  }

  /**
   * Validate access to publish or unpublish form.
   *
   * @param EntityInterface $entity
   * @param string $operation
   *
   * @return bool
   */
  public function validateEntityAccess(EntityInterface $entity, $operation) {
    $access = FALSE;
    $entity_type = $entity->getEntityTypeId();
    $entity_types = $this->wmePublishConfigManager->getEntityTypes();
    if (in_array($entity_type, $entity_types)) {
      $bundle = $entity->bundle();
      $bundles_enabled = $this->wmePublishConfigManager->getEnableBundles($entity_type);
      $isPublished = $this->entityStatus->getEntityStatus($entity);
      $publish = $operation == 'publish' && !$isPublished;
      $unpublish = $operation == 'unpublish' && $isPublished;

      if (($publish || $unpublish) && in_array($bundle, $bundles_enabled)) {
        $access = TRUE;
      }
      // Invoke hook_wme_publish_access_alter.
      $this->moduleHandler->alter('wme_publish_access', $access, $entity, $operation);
    }
    return $access;
  }

}
