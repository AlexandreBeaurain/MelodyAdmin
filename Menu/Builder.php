<?php
namespace Melody\AdminBundle\Menu;

use Admingenerator\GeneratorBundle\Menu\AdmingeneratorMenuBuilder;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class Builder extends AdmingeneratorMenuBuilder
{

    protected $factory;
    protected $container;

    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    public function createAdminMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
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
                $controller = new $controllerClass();
                if (
                    $controller instanceof \Admingenerator\GeneratorBundle\Controller\Doctrine\BaseController ||
                    $controller instanceof \Admingenerator\GeneratorBundle\Controller\Propel\BaseController
                ) {
                    var_dump($controllerClass);
                    $menuElements['Test'][$routeName] = $routeName;
                }
            }
        }
        $menu->setChildrenAttributes(array('id' => 'main_navigation', 'class' => 'nav navbar-nav'));
        foreach( $menuElements as $menuGroup => $menuElementsPerGroup ) {
            $group = $this->addDropdown($menu, $menuGroup);
            foreach( $menuElementsPerGroup as $label => $route ) {
                $this->addLinkRoute($group, $label, $route);
            }
        }
        return $menu;
    }
}