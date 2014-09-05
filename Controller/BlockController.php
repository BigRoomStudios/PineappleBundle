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
    FOS\RestBundle\Controller\Annotations\Get,
    FOS\RestBundle\Controller\Annotations\Post,
    FOS\RestBundle\Controller\Annotations\Route,
    FOS\RestBundle\Controller\FOSRestController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Sonata\BlockBundle\Block\BlockContext;

use Sonata\AdminBundle\Form\FormMapper;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * 
 */
class BlockController extends FosRestController
{
	
	/**
	 * Generates a form that can be used to set the settings for a given widget type.
	 * 
	 * @Get("/api/block/{blockServiceId}", name="api_block_get")
	 * @View(template="BRSPineappleBundle:Form:form.html.twig")
	 */
	public function getAction(Request $request, $blockServiceId) {
		
		try {
			
			//get the block service
			$blockService = $this->container->get($blockServiceId);
			
		/* I think there is an better way to deal with exceptions.  Check it: https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/4-exception-controller-support.md */
		} catch(ServiceNotFoundException $e) {
			
			//if no block service was found, return an error
			throw new HttpException(404, "The requested block type was not found");
			
		}
		
		//get the block manager
		$blockManager = $this->container->get('brs.page.manager.block');
		
		//get a block Context Interface
		$block = $blockManager->createWidget($blockServiceId);
		
		$settings = $block->getSettings();
		$params = $request->query->all();
		if(!empty($params)) {
			$settings = array_merge($settings, $params);
		}
		
		$block->setSettings($settings);
		
		$blockContext = new BlockContext($block, $settings);
		
		//execute the block service
		$response = $blockService->execute($blockContext);
		
		return $response;
		
	}
	
	/**
	 * Generates a form that can be used to set the settings for a given widget type.
	 * 
	 * @Get("/api/block/{id}/settings", name="api_block_get_settings")
	 * @View(template="BRSPineappleBundle:Form:block-settings.html.twig")
	 */
	public function getSettingsAction($id) {
		
		$block = $this->getBlock($id);
		$form = $this->getBlockSettingsForm($block);
		
		$data = array(
			'form' => $form,
			'block' => $block,
		);
		
		//return the form
		return $data;
		
	}
	
	/**
	 * Transforms a selection widget into the given type
	 * 
	 * @Post("/api/block/{id}/settings.{_format}", name="api_post_widget_settings", defaults={"_format"="html"})
	 * @View(template="BRSPineappleBundle:Form:test.html.twig")
	 * 
	 * @ApiDoc(
	 *  section="Widget",
     *  description="This endpoint will transform a selection widget into the requested type",
	 *  statusCodes={
	 *  	200="Returned when successful",
	 *  	404="Returned when blockServiceId is not found"
	 *  },
	 *  requirements={
	 * 		{ "name"="blockServiceId", "dataType"="string", "description"="id of the service for the type of block to get a settings edit form.  Example: 'brs.block.service.map'" }
	 *  }
     * )
	 */
	public function postSettingsAction(Request $request, $id) {
		
		//get the edit form
		$block = $this->getBlock($id);
		$form = $this->getBlockSettingsForm($block);
		
		$form->handleRequest($request);
	    
		if($form->isValid()) {
			
			$block = $form->getData();
			$em = $this->getDoctrine()->getManager();
			
			$em->persist($block);
			$em->flush();
			
			$data = array(
				'block' => $block,
			);
			
			$view = $this->view($data, 200)
						 ->setTemplate($block->getSetting('template'));
			
			return $this->handleView($view);
			
	    } else {
	    	
	    	foreach($form->getErrors() as $error) {
	    		print($error->getMessage());
			}
			
			die('test');
	    	
	    }
	    
	}
	
	private function getBlock($id) {
		
		$blockManager = $this->container->get('brs.page.manager.block');
		$block = $this->getDoctrine()->getRepository('ApplicationSonataPageBundle:Block')->find($id);
		
		return $block;
		
	}
	
	private function getBlockSettingsForm($block) {
		
		//$block = $this->getDoctrine()->getRepository('ApplicationSonataPageBundle:Block')->find($id);
		$blockService = $this->container->get($block->getType());
		
		//get the block admin
		$blockAdmin = $this->container->get('sonata.page.admin.block');
		
		//create the form builder
        $formBuilder = $blockAdmin->getFormContractor()->getFormBuilder(
            'api_post_widget_settings',
            array(
            	'data_class' => $blockAdmin->getClass(),
            	'action' => $this->container->get('router')->generate('api_post_widget_settings', array('id' => $block->getId(), '_format' => 'html')),
            	'method' => 'POST',
            	'attr' => array(
            		'id' => 'widget_settings_' . $block->getId(),
            		'class' => 'form_ajax',
            		'data-success-event' => 'widget_settings_saved',
            		'data-fail-event' => 'widget_settings_failed',
            	),
			)
        );
		
		//create the form mapper
		$mapper = new FormMapper($blockAdmin->getFormContractor(), $formBuilder, $blockAdmin);
		
		//build the edit form
		$blockService->buildEditForm($mapper, $block);
		
		//get the form builder
		$builder = $mapper->getFormBuilder();
		
		//update the data
		$builder->setData($block);
		
		return $builder->getForm();
		
	}
	
}
