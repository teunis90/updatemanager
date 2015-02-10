<?php
// src/Acme/DemoBundle/Menu/Builder.php
namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
		$request = $this->container->get('request');
		$id = !is_null($request->get('id')) ? $request->get('id') : 1;
	    
        $menu = $factory->createItem('root');
		$menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Overview', array('route' => 'index'));
        $menu->addChild('Hosts', array('route' => 'host'));
	        $menu['Hosts']->addChild('Edit Host', array('route' => 'hostEdit', 'routeParameters' => array('id' => $id )) );
			$menu['Hosts']->addChild('New Host', array('route' => 'hostNew'));
        $menu->addChild('Keychain', array('route' => 'keychain'));
	        $menu['Keychain']->addChild('Edit Host', array('route' => 'keychainEdit', 'routeParameters' => array('id' => $id )) );
			$menu['Keychain']->addChild('New Host', array('route' => 'keychainNew'));

        return $menu;
    }
}