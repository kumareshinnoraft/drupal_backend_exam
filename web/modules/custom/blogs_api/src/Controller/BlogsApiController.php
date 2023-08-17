<?php

namespace Drupal\blogs_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Returns responses for practice_test routes.
 */
class BlogsApiController extends ControllerBase {

  /**
   * This is entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * This is a constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   This will be used to fetch the nodes.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Builds the response.
   */
  public function build(Request $request) {

    $author = $request->get('author');
    $year = $request->get('year');
    $tags = $request->get('tags');

    if (!empty($author) || !empty($year) || !empty($tags)) {
      $headers = $request->headers->get('api-key');
      if ($headers !== 'abc') {
        throw new AccessDeniedHttpException();
      }
    }

    $blogs = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->condition('type', 'blogs')
      ->accessCheck(FALSE);

    if (!empty($author)) {

      // Load user by username.
      $users = $this->entityTypeManager->getStorage('user')->loadByProperties(['name' => $author]);

      if (!empty($users)) {
        // Get the first user entity.
        $user = reset($users);
        $uid = $user->id();
      }
      $blogs->condition('uid', $uid);

    }

    if (!empty($year)) {
      $blogs->condition('created', strtotime('01/01/' . $year), '>=');
      $blogs->condition('created', strtotime('12/31/' . $year), '<=');
    }

    if (!empty($tags)) {

      $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['name' => $tags]);

      $termIds = [];
      foreach ($terms as $term) {
        $termIds[] = $term->id();
      }

      $blogs->condition('field_blog_tags', $termIds[0], 'IN');
    }
    $nodes = $blogs->execute();
    $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($nodes);

    // Construct the data you want to return in the API response.
    $data = [
      'title' => NULL,
      'data' => [],
    ];

    // Populate 'data' with information from each node.
    foreach ($nodes as $node) {

      $tags = $node->get('field_blog_tags')->getValue();
      $tag_names = [];

      foreach ($tags as $tag) {
        $terms = $this->entityTypeManager->getStorage('taxonomy_term')->load($tag['target_id']);
        if ($term) {
          $tag_names[] = $term->getName();
        }
      }

      $author_id = $node->getOwnerId();
      $author = $this->entityTypeManager->getStorage('user')->load($author_id);
      $author_name = $author->getAccountName();

      $timestamp = $node->get('created')->value;
      $created_date = new DrupalDateTime();
      $created_date->setTimestamp($timestamp);
      $year = $created_date->format('Y');

      $data['data'][] = [
        'title' => $node->getTitle(),
        'body' => $node->get('body')->value,
        'Published Date' => $year,
        'author_name' => $author_name,
        'tags' => $tag_names,
      ];
    }
    return new JsonResponse($data);
  }

  /**
   * Get tag names from a node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node entity.
   *
   * @return array
   *   An array of tag names.
   */
  public function getTagNames(NodeInterface $node) {
    $tag_names = [];
    $tags = $node->field_tags->referencedEntities();

    foreach ($tags as $tag) {
      $tag_names[] = $tag->getName();
    }

    return $tag_names;
  }

}
