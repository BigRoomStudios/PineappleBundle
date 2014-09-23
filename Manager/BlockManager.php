<?php

namespace BRS\PineappleBundle\Manager;

use Sonata\PageBundle\Entity\BlockManager as BaseBlockManager;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockManager extends BaseBlockManager
{
	protected $container;
	
	protected $entityManager;

    protected $class;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param string                      $class
     */
    public function __construct(Container $container, $entityManager, $class)
    {
    	parent::__construct($this->class, $entityManager);
		
    	$this->container = $container;
        $this->entityManager = $entityManager;
        $this->class         = $class;
		$this->pineapples    = array();
    }
	
	// public function getObjectManager() {
		// error_log('test');
	// }
	
	public function addPineapple($id, $pineapple) {
		
		//convert categories from a CSV to an array
		if(isset($pineapple['categories'])) {
			$pineapple['categories'] = explode(',', $pineapple['categories']);
		}
		
		//add the pineapple to the list
		$this->pineapples[$id] = $pineapple;
		
	}
	
	public function getPineapple($id) {
		
		if(isset($this->pineapples[$id])) {
			return $this->pineapples[$id];
		}
		
		return null;
		
	}
	
	public function getPineapples($category) {
		
		$return = array();
		
		foreach($this->pineapples as $pineapple) {
			if(isset($pineapple['categories']) && in_array($category, $pineapple['categories'])) {
				$return[] = $pineapple;
			}
		}
		
		return $return;
		
	}
	
	public function getPineappleCategories() {
		
		$cats = array();
		
		foreach($this->pineapples as $pineapple) {
			if(isset($pineapple['categories'])) {
				foreach($pineapple['categories'] as $cat) {
					if(!in_array($cat, $cats)) {
						$cats[] = $cat;
					}
				}
			}
		}
		
		return $cats;
		
	}
	
	public function createContainerBlock($page, $code='content') {
		
		$blockInteractor = $this->container->get('sonata.page.block_interactor');
		
		$container = $blockInteractor->createNewContainer(array(
			'enabled' => true,
			'page' => $page,
			'code' => $code,
		));
		
		$container->setSetting('template', 'BRSCoreBundle:Blocks:container.html.twig');
		$container->setType('brs.block.service.container');
		$container->setPage($page);
		
		return $container;
		
	}
	
	public function addSelectionBlock($container) {
		
        //add an empty block to the container
        $container->addChildren($selection = $this->createSelectionBlock());
		
		//establish relationship with the empty block to the page
		$page = $container->getPage();
		$page->addBlocks($selection);
        $selection->setPage($page);
        
		//get all the container's children
		$container_children = $container->getChildren();
		$num_children = count($container_children);
		$col_width = 12 / $num_children;
		
		//set some settings on the empty block
        $selection->setPosition($num_children);
        $selection->setEnabled(true);
		
		//adjust the size of each column
		foreach($container_children as $child) {
			
			$child->setSetting('width', "col-$col_width");
			
		}
		
		//set the page
		$selection->setPage($page);
		
	}
	
	/**
	 * 
	 */
	public function createAndAddWidget($container, $widgetServiceName, $settings=array()) {
		
		//create a new widget
		$widget = $this->createWidget($widgetServiceName, $settings);
		
		//add it to the container
		$this->addWidget($container, $widget);
		
		return $widget;
		
	}
	
	/**
	 * Adds a widget to a container
	 * 
	 * @param container - container to add the widget to
	 * @param widget - widget to add
	 * 
	 * @return success or failure - true or false
	 */
	public function addWidget($container, $widget) {
		
        //add the widget to the container
        $container->addChildren($widget);
		
		//add the widget to the page and set the page on the widget
		$page = $container->getPage();
		$page->addBlocks($widget);
        $widget->setPage($page);
        
		//determine the width of each child
		$container_children = $container->getChildren();
		$num_children = count($container_children);
		$col_width = 12 / $num_children;
		
		//set the size of each child
		foreach($container_children as $child) {
			$child->setSetting('width', "col-$col_width");
		}
		
		//set some settings on the empty block
        $widget->setPosition($num_children);
        $widget->setEnabled(true);
		
		//assume success...this should be updated
		return true;
		
	}
	
	/**
	 * This function creates a widget, initializes its settings and returns the new widget
	 * 
	 * @param widgetServiceName - the name of the widget service
	 * @param settings - optional parameter - array of settings that override the default set by the widget service
	 * 
	 * @return a new widget
	 */
	public function createWidget($widgetServiceName, $settings=array()) {
		
		//create the block
		$widget = $this->create();
		$widget->setType($widgetServiceName);
		
		$resolver = new OptionsResolver($pineapple_options);
		
		//get the widget service
		$widgetService = $this->container->get($widgetServiceName);
		
		//initialize the settings
		$widgetService->setDefaultSettings($resolver);
		$widget->setSettings($resolver->resolve($settings));
		
		
		if(($pineapple_options = $this->getPineapple($widgetServiceName)) && isset($pineapple_options['template'])) {
			$widget->setSetting('template', $pineapple_options['template']);
		}
		
		//return the new widget
		return $widget;
		
	}
	
	public function createSelectionBlock() {
		
		$block = $this->create();
		$block->setType('brs.block.service.selection');
		
		$block->setSetting('template', 'BRSCoreBundle:Blocks:selection.html.twig');
		
		return $block;
		
	}
	
}