<?php

namespace Drupal\custom_like_button\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a LikeButtonForm form.
 */
class LikeButtonForm extends FormBase {

  /**
   * This is entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * This is a constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   This will be used to fetch the nodes.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The RouteMatchInterface service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteMatchInterface $route_match) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_likes_like_button_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node_id = NULL) {

    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      $node_id = $node->id();
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Like'),
      '#submit' => ['::submitForm'],
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'event' => 'click',
      ],
      '#attributes' => ['class' => ['like-button']],
      '#node_id' => $node_id,
    ];

    // Display the current likes count.
    $likes_count = $this->getLikesCount($node_id);
    $form['likes_count'] = [
      '#markup' => $this->t('Likes: @count', ['@count' => $likes_count]),
      '#prefix' => '<div id="likes_count">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node_id = $form_state->getTriggeringElement()['#node_id'];
    $likes_count = $this->getLikesCount($node_id);
    $likes_count++;

    // Update the likes count on the node.
    $node = $this->entityTypeManager->getStorage('node')->load($node_id);
    $node->set('likes_count', $likes_count);
    $node->save();
  }

  /**
   * Ajax callback to update the likes count.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $node_id = $form_state->getTriggeringElement()['#node_id'];
    $likes_count = $this->getLikesCount($node_id);

    // Prepare the updated likes count markup.
    $updated_likes_count = $this->t('Likes: @count', ['@count' => $likes_count]);

    // Create an Ajax response to update the likes count element.
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand('#likes_count', $updated_likes_count));
    return $response;
  }

  /**
   * Get the current likes count for a node.
   */
  protected function getLikesCount($node_id) {
    // Load the node and retrieve the likes count field value.
    $node = $this->entityTypeManager->getStorage('node')->load($node_id);
    return $node->get('likes_count')->value;
  }

}
