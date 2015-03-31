<?php
namespace Melody\AdminBundle\Menu;

use Admingenerator\GeneratorBundle\Menu\DefaultMenuBuilder;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\Container;

class Builder extends DefaultMenuBuilder
{

    public function sidebarMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array('class' => 'sidebar-menu'));
        if (
            ! ( $securityContext = $this->container->get('security.context') ) || 
            ! ( $securityContext->isGranted('IS_AUTHENTICATED_FULLY') )
        ) {
            return $menu;
        }
        if ($dashboardRoute = $this->container->getParameter('admingenerator.dashboard_route')) {
            $this
                ->addLinkRoute($menu, 'admingenerator.dashboard', $dashboardRoute)
                ->setExtra('icon', 'fa fa-dashboard');
        }
        $router = $this->container->get('router');
        $collection = $router->getRouteCollection();
        $allRoutes = $collection->all();
        $menuElements = array();
        foreach( $allRoutes as $routeName => $route ) {
            /* @var $route \Symfony\Component\Routing\Route */
            $defaults = $route->getDefaults();
            if (
                strpos( $route->getPath(), '/admin' ) === 0 &&
                strpos( $routeName, '_list' ) > 0 &&
                isset($defaults['_controller'])
            ) {
                $controllerAction = explode(':', $defaults['_controller']);
                $controllerClass = $controllerAction[0];
                list($none,$bundleName,$none, $entity, $none) = explode('\\',$controllerClass,6);
                $bundleName = ucfirst( strtr( Container::underscore( strtr( $bundleName, array('Bundle'=>'') ) ), array('_'=>' ') ) );
                $entity = ucfirst( strtr( Container::underscore( $entity ), array('_'=>' ') ) );
                $controller = new $controllerClass();
                if (
                    $controller instanceof \Admingenerator\GeneratorBundle\Controller\Doctrine\BaseController ||
                    $controller instanceof \Admingenerator\GeneratorBundle\Controller\Propel\BaseController
                ) {
                    $menuElements[$bundleName][$entity] = $routeName;
                }
            }
        }
        foreach( $menuElements as $menuGroup => $menuElementsPerGroup ) {
            $group = $this->addDropdown($menu, $menuGroup);
            foreach( $menuElementsPerGroup as $label => $route ) {
                $this->addLinkRoute($group, $label, $route);
            }
        }
        return $menu;
    }
}