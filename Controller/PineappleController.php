<?php

namespace BRS\PineappleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Sonata\AdminBundle\Form\FormMapper;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * @Prefix("api")
 * @NamePrefix("api_")
 * Following annotation is redundant, since FosRestController implements ClassResourceInterface
 * so the Controller name is used to define the resource. However with this annotation its
 * possible to set the resource to something else unrelated to the Controller name
 * @RouteResource("Pineapple")
 */
class PineappleController extends FosRestController
{
	
	public function getAction(Request $request, $category) {
		
		//get the block manager
		$blockManager = $this->container->get('brs.page.manager.block');
		
		//get the pineapples for the given category
		$pineapples = $blockManager->getPineapples($category);
		
		//setup the view
		$view = $this->view($pineapples, 200)
					 ->setTemplate("BRSPineappleBundle:Pineapple:pineapples.html.twig")
					 ->setTemplateVar('pineapples');
		
		return $this->handleView($view);
		
	}
	
	/**
     * Display the form
     *
     * @return Form form instance
     *
     * 
	 * @View(template="BRSCoreBundle:REST:create_page.html.twig")
     */
	public function newPageAction($site_id) {
		
		$pageManager = $this->container->get('sonata.page.manager.page');
		$page = $pageManager->create();
		
        $form = $this->getPageForm($page, $site_id);
		
		return $form;
		
	}
	
}
