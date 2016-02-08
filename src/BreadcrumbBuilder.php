<?php

namespace Drupal\current_page_crumb;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Link;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\system\PathBasedBreadcrumbBuilder;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * {@inheritdoc}
 */
class BreadcrumbBuilder extends PathBasedBreadcrumbBuilder {

  protected $menuActiveTrail;

  protected $menuLinkManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestContext $context, AccessManagerInterface $access_manager, RequestMatcherInterface $router, InboundPathProcessorInterface $path_processor, ConfigFactoryInterface $config_factory, TitleResolverInterface $title_resolver, AccountInterface $current_user, CurrentPathStack $current_path) {
    parent::__construct($context, $access_manager, $router, $path_processor, $config_factory, $title_resolver, $current_user, $current_path);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumbs = parent::build($route_match);

    $request = \Drupal::request();
    $path = trim($this->context->getPathInfo(), '/');
    $path_elements = explode('/', $path);

    if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
      $title = \Drupal::service('title_resolver')->getTitle($request, $route);
      if (!isset($title)) {
        // Fallback to using the raw path component as the title if the
        // route is missing a _title or _title_callback attribute.
        $title = str_replace(array('-', '_'), ' ', Unicode::ucfirst(end($path_elements)));
      }
      $breadcrumbs->addLink(Link::createFromRoute($title, '<none>'));
    }

    return $breadcrumbs;
  }
}
