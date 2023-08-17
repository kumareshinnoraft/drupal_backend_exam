<?php

namespace Drupal\otp_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;

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
   * BudgetMenuNodeViewSubscriber constructor.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
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
      $form_state->setErrorByName('otp does not match');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
