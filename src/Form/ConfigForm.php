<?php

namespace Drupal\wme_publish\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Configuration form to Wme Publish module.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\EntityTypeBundleInfo definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfo
   */
  protected $entityTypeBundleInfo;

  /**
   * Drupal\Core\Cache\CacheTagsInvalidator definition.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidator
   */
  protected $cacheTags;

  /**
   * Drupal\Core\Routing\RouteBuilder definition.
   *
   * @var \Drupal\Core\Routing\RouteBuilder
   */
  protected $routeBuilder;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entityTypeBundleInfo = $container->get('entity_type.bundle.info');
    $instance->cacheTags = $container->get('cache_tags.invalidator');
    $instance->routeBuilder = $container->get('router.builder');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'wme_publish.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('wme_publish.settings');
    $config_bundles = $config->get('enable_bundles');

    $form['entity_types'] = [
      '#type' => 'details',
      '#title' => $this->t('Content entity types'),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    foreach ($this->entityTypeManager->getDefinitions() as $definition) {
      $isContent = $definition instanceof ContentEntityTypeInterface;
      $isRevisionable = $definition->isRevisionable();
      $isPublished = $definition->hasKey('published');
      if ($isContent && $isRevisionable && $isPublished) {
        $id = $definition->id();
        $default_value = isset($config_bundles[$id]) ? $config_bundles[$id] : [];
        $form['entity_types'][$id] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('@entity types', ['@entity' => $definition->getLabel()]),
          '#default_value' => $default_value,
          '#options' => $this->buildEntityList($id),
        ];
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $entity_types = $form_state->getValue('entity_types');
    $this->config('wme_publish.settings')
      ->set('enable_bundles', $this->getEnableBundles($entity_types))
      ->save();
    $this->cacheTags->invalidateTags(['local_task']);
    $this->routeBuilder->rebuild();
  }

  /**
   * Return an array with the selected bundles.
   *
   * @param array $entity_types
   *   Entity types array.
   *
   * @return array
   *   Array of enable bundles.
   */
  private function getEnableBundles(array $entity_types) : array {
    $enable_bundles = [];
    foreach ($entity_types as $entity_type => $bundles) {
      foreach ($bundles as $bundle) {
        if ($bundle) {
          $enable_bundles[$entity_type][] = $bundle;
        }
      }
    }
    return $enable_bundles;
  }

  /**
   * Builds a list containing all bundles for a given entity type.
   */
  private function buildEntityList($entity_type_id) {
    $entityList = [];
    $bundleInfo = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    foreach ($bundleInfo as $bundle_name => $bundle) {
      $entityList[$bundle_name] = $bundle['label'];
    }
    return $entityList;
  }

}
