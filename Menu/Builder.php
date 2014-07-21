<?php
namespace Melody\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class Builder
{

    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createAdminMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');
        $router = $this->container->get('router');
        $collection = $router->getRouteCollection();
        $allRoutes = $collection->all();
        foreach( $allRoutes as $route => $params ) {
            if ( isset($params['type']) && $params['type'] == 'admingenerator' ) {
                $menu->addChild('Module x', array(
                    'route' => $route
                ));
            }
        }
        return $menu;
    }
}