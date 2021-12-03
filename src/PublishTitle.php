<?php

namespace Drupal\wme_publish;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Build the Publish page title.
 */
class PublishTitle {

  use StringTranslationTrait;

  /**
   * Drupal\Core\Routing\CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * Drupal\Core\Entity\EntityTypeBundleInfo definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a new PublishTitle object.
   */
  public function __construct(
    CurrentRouteMatch $current_route_match,
    EntityTypeBundleInfo $entity_type_bundle
    ) {
    $this->currentRouteMatch = $current_route_match;
    $this->entityTypeBundleInfo = $entity_type_bundle;
  }

  /**
   * Generate Publish/Unpublish Title.
   *
   * @param string $entity_type
   *   Entity type striing.
   * @param mixed $operation
   *   Operation string.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Return generated title.
   */
  public function getTitle($entity_type = "", $operation = "") : TranslatableMarkup {

    $entity = $this->currentRouteMatch->getParameter($entity_type);
    $bundleInfo = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
    if ($entity instanceof EntityInterface) {
      return $this->t('Are you sure you want to <b>@operation</b> the @label_type <em>@label</em>. ID: @entity_id. Revision ID: @revision_id?', [
        '@operation' => $operation,
        '@label_type' => $bundleInfo[$entity->bundle()]['label'],
        '@label' => $entity->label(),
        '@entity_id' => $entity->id(),
        '@revision_id' => $entity->getRevisionId(),
      ]);
    }
  }

}
