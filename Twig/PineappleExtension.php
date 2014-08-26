<?php

// src/Acme/DemoBundle/Twig/AcmeExtension.php
namespace BRS\PineappleBundle\Twig;

class PineappleExtension extends \Twig_Extension
{
	
    /**
     * @param $configurator
     */
    public function __construct($configurator) {
    	
        $this->configurator = $configurator;
		
    }
	
	/**
	 * Returns a list of functions to add to the existing list.
	 * 
	 * @return array An array of functions
	 */
	public function getFunctions() {
		
		return array(
			'pineapple_initialize' => new \Twig_Function_Method($this, 'initialize'),
		);
		
	}
	
	/**
	 * @return void
	 */
	public function initialize() {
		
		$config = $this->configurator->getConfiguration();
		
		echo sprintf("<script type='text/javascript'>pineapple_admin_json = $config;</script>");
	}
	
	/**
	 * 
	 */
	public function getName() {
		return 'pineapple_extension';
	}
	
}