<?php

namespace Drupal\custom_like_button\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a related blogs block.
 *
 * @Block(
 *   id = "custom_like_button_related_blogs",
 *   admin_label = @Translation("Related Blogs"),
 *   category = @Translation("Custom"),
 * )
 */
final class RelatedBlogsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cache factory.
   *
   * @var \Drupal\Core\Cache\CacheFactoryInterface
   */
  protected $cacheFactory;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new RelatedBlogsBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Cache\CacheFactoryInterface $cache_factory
   *   The cache factory service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The RouteMatchInterface service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, CacheFactoryInterface $cache_factory, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheFactory = $cache_factory;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('cache_factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {

    $cache_key = 'custom_related_blogs_block';
    $cache_expire = 600;

    // Check if cached data is available.
    if ($cache = $this->cacheFactory->get('cache_backend_id')->get('cache_key')) {
      $related_blogs = $cache->data;
    }
    else {

      $build = [];

      $node = $this->routeMatch->getParameter('node');
      if ($node) {
        $author_id = $node->getOwnerId();
        $query = $this->entityTypeManager->getStorage('node')->getQuery()
          ->condition('type', 'blogs')
          ->condition('uid', $author_id)
          ->accessCheck(TRUE)
          ->sort('likes_count', 'DESC')
          ->range(0, 3);

        $related_blogs_ids = $query->execute();

        if (!empty($related_blogs_ids)) {
          $related_blogs = $this->entityTypeManager->getStorage('node')->loadMultiple($related_blogs_ids);

          foreach ($related_blogs as $related_blog) {
            $related_blog_title = $related_blog->getTitle();
            $related_blog_url = Url::fromRoute('entity.node.canonical', ['node' => $related_blog->id()]);
            $build['related_blogs'][] = [
              '#markup' => '<a href=' . $related_blog_url->toString() . '>' . $related_blog_title . '</a><br>',
            ];

            $cache_tags = ['custom_related_blogs'];
            $this->cacheFactory->get('cache_backend_id')->set($cache_key, $related_blogs, $cache_expire, $cache_tags);
          }
        }
      }

    }
    return $build;
  }

}
