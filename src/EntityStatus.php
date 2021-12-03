<?php

namespace Drupal\wme_publish;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Class EntityStatus.
 */
class EntityStatus {

  /**
   * Get Entity Status.
   *
   * @param  ContentEntityInterface $entity
   * @return bool
   */
  public function getEntityStatus(ContentEntityInterface $entity) : bool {
    $status = TRUE;
    $entity_type = $entity->getEntityType();
    $publish_key = $entity_type->hasKey('published') ? $entity_type->getKey('published') : NULL;
    if ($publish_key) {
      $status = (bool) $entity->get($publish_key)->value;
    }
    return $status;
  }

  /**
   * Set Entity Status.
   *
   * @param  ContentEntityInterface $entity
   * @return void
   */
  public function setEntityStatus(ContentEntityInterface &$entity, bool $status) : void {
    $entity_type = $entity->getEntityType();
    $publish_key = $entity_type->hasKey('published') ? $entity_type->getKey('published') : NULL;
    if ($publish_key) {
      $entity->set($publish_key, (int) $status);
    }
  }

}
