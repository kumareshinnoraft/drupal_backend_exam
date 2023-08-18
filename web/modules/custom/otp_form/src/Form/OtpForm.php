<?php

namespace Drupal\otp_form\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Otp form form.
 */
final class OtpForm extends FormBase {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * BudgetMenuNodeViewSubscriber constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   This will be used to fetch the nodes.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The RouteMatchInterface service.
   */
  public function __construct(StateInterface $state, EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user) {
    $this->state = $state;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'otp_form_otp';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['otp'] = [
      '#type' => 'textfield',
      '#title' => $this->t('enter otp here'),
      '#required' => TRUE,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $otp = $this->state->get('stored_paths', []);

    if ($form_state->getValue('otp') !== $otp) {
      $this->messenger()->addMessage($this->t('otp does not match.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $user->set('field_email_status', 1);
    $user->save();

    $this->messenger()->addMessage($this->t('Whenever account will activated we will inform you.'));
    $form_state->setRedirect('<front>');
  }

}
