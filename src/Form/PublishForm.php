<?php

namespace Drupal\wme_publish\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Publish and Unpublish Form.
 */
class PublishForm extends FormBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Routing\RouteMatchInterface definition.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Drupal\Core\Messenger\Messenger definition.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Drupal\wme_publish\EntityPublisher definition.
   *
   * @var \Drupal\wme_publish\EntityPublisher
   */
  protected $entityPublisher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->currentRouteMatch = $container->get('current_route_match');
    $instance->messenger = $container->get('messenger');
    $instance->entityPublisher = $container->get('wme_publish.entity_publisher');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'publish_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type = NULL, $operation = 'publish') {
    $entity_id = $this->currentRouteMatch->getRawParameter($entity_type);

    $form['entity_type'] = [
      '#type' => 'hidden',
      '#value' => $entity_type,
    ];

    $form['entity_id'] = [
      '#type' => 'hidden',
      '#value' => $entity_id,
    ];

    $form['operation'] = [
      '#type' => 'hidden',
      '#value' => $operation,
    ];

    $form['submit'] = [
      '#attributes' => [
        'class' => ['button--primary'],
      ],
      '#type' => 'submit',
      '#value' => $operation === 'publish'
      ? $this->t('Publish')
      : $this->t('Unpublish'),
    ];

    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromRoute("entity.$entity_type.canonical", [$entity_type => $entity_id]),
      '#cache' => [
        'contexts' => [
          'url.query_args:destination',
        ],
      ],
    ];

    $form['#cache']['tags'][] = $entity_type . '_list:' . $entity_type;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $entity_type = $form_state->getValue('entity_type');
    $entity_id = $form_state->getValue('entity_id');
    $operation = $form_state->getValue('operation');
    $entity = $this->getEntityObject($entity_type, $entity_id);
    $status = $operation === 'publish' ? TRUE : FALSE;

    $this->entityPublisher->setEntityStatus($entity, $operation);

    $this->messenger->addMessage(
      $this->t('The content <em>@label</em> is being @operation.', [
        '@label' => $entity->label(),
        '@operation' => $status,
      ])
    );

    $form_state->setRedirectUrl(Url::fromRoute("entity.$entity_type.canonical", [$entity_type => $entity_id]));
  }

  /**
   * Get Entity Object.
   *
   * @param string $entity_type
   *   Entity type string.
   * @param string $entity_id
   *   Entity identiifier.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity returned.
   */
  protected function getEntityObject($entity_type, $entity_id) : EntityInterface {
    $entity = NULL;
    if ($entity_type && $entity_id) {
      $entity = $this->entityTypeManager
        ->getStorage($entity_type)
        ->load($entity_id);
    }
    return $entity;
  }

}
