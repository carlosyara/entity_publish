<?php

namespace Drupal\wme_publish;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

/**
 * Publish/Unpublish entities.
 */
class EntityPublisher {

  /**
   * Drupal\wme_publish\EntityStatus definition.
   *
   * @var \Drupal\wme_publish\EntityStatus
   */
  protected $entityStatus;

  /**
   * Drupal\Core\Extension\ModuleHandlerInterface definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal\Core\Cache\CacheTagsInvalidatorInterface definition.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a new EntityPublisher object.
   */
  public function __construct(EntityStatus $wme_publish_entity_status, ModuleHandlerInterface $module_handler, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->entityStatus = $wme_publish_entity_status;
    $this->moduleHandler = $module_handler;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * Sets the entity status.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to be processed.
   * @param string $operation
   *   Operation must be type (publish || unpublish).
   */
  public function setEntityStatus(ContentEntityInterface &$entity, string $operation) : void {
    $entity_type = $entity->bundle();
    $status = $operation === 'publish' ? TRUE : FALSE;

    $entity->setNewRevision(FALSE);
    $this->entityStatus->setEntityStatus($entity, $status);

    // Invoke hook_entity_wme_prepublish / hook_entity_wme_preunpublish.
    $this->moduleHandler->invokeAll('entity_wme_pre' . $operation, [$entity]);
    // Invoke hook_ENTITY_TYPE_wme_prepublish/hook_ENTITY_TYPE_wme_preunpublish.
    $this->moduleHandler->invokeAll($entity_type . '_wme_pre' . $operation, [$entity]);
    $entity->save();

    // Invoke hook_entity_wme_publish / hook_entity_wme_unpublish.
    $this->moduleHandler->invokeAll('entity_wme_' . $operation, [$entity]);
    // Invoke hook_ENTITY_TYPE_wme_publish / hook_ENTITY_TYPE_wme_unpublish.
    $this->moduleHandler->invokeAll($entity_type . '_wme_' . $operation, [$entity]);
    // Invoke hook_entity_wme_post_publish/hook_entity_wme_post_unpublish.
    $this->moduleHandler->invokeAll('entity_wme_post_' . $operation, [$entity]);

    $this->cacheTagsInvalidator->invalidateTags($entity->getCacheTags());
    $this->cacheTagsInvalidator->invalidateTags(['local_task']);
  }

}
