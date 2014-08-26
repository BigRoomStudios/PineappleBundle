<?php

namespace BRS\PineappleBundle\Configurator;

use Symfony\Component\Yaml\Yaml;

/**
 * Role Configurator
 * 
 * @author Brian Leighton <brian@bigroomstudios.com
 */
class RoleConfigurator
{
	
	/**
     * @param $configurator
     */
    public function __construct($container) {
    	
        $this->container = $container;
		$this->config_path = $container->getParameter('brs_pineapple.config_path');
		
    }
	
	
	public function getConfiguration() {
		
		$yaml = Yaml::parse($this->config_path);
		
		$kernel = $this->container->get('kernel');
		$config = file_get_contents($kernel->locateResource($this->config_path));
		
		$config_json = json_decode($config, true);
		
		$actions = array();
		
		$user = $this->container->get('security.context')->getToken()->getUser();
		
		foreach($config_json['actions'] as $action) {
			
			if($action['roles']) {
				
				foreach($action['roles'] as $role) {
					if($user->hasRole($role)) {
						$actions[] = $action;
						break;
					}
				}
				
			} else {
				$actions[] = $action;
			}
			
		}
		
		$config_json['actions'] = $actions;
		
		$return = json_encode($config_json);
		
		return $return;
	}
	
}