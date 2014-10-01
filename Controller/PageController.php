<?php

namespace BRS\PineappleBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Symfony\Bundle\FrameworkBundle\Console\Application,
    Symfony\Component\Console\Input\StringInput,
    Symfony\Component\Console\Output\StreamOutput;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Application\Sonata\PageBundle\Entity\Page,
    BRS\PineappleBundle\Form\Type\PageType;
	
use Sonata\PageBundle\Model\SiteInterface;

/**
 * @Prefix("api")
 * @NamePrefix("api_")
 * Following annotation is redundant, since FosRestController implements ClassResourceInterface
 * so the Controller name is used to define the resource. However with this annotation its
 * possible to set the resource to something else unrelated to the Controller name
 * @RouteResource("Page")
 */
class PageController extends FosRestController
{
	
	public function getAddWidgetTypeAction(Request $request, $page, $container, $type) {
		
		$em = $this->getDoctrine()->getManager();
		
		//get the page
		$page = $em->getRepository('ApplicationSonataPageBundle:Page')->find($page);
		
		//get the container
		$container = $em->getRepository('ApplicationSonataPageBundle:Block')->find($container);
		
		//add the block to the container
		$blockManager = $this->container->get('brs.page.manager.block');
		$block = $blockManager->createAndAddWidget($container, $type);
		
		//save the new block
		$em->persist($container);
		$em->flush();
		
		//render the template
		$html = $this->container->get('templating')->render($block->getSetting('template'), array(
            'block' => $block,
        ));
		
		//get the html code
		$result = array(
			'success' => true,
			'html' => $html,
			'id' => $block->getId(),
			'classes' => $block->getSetting('classes'),
		);
		
        return $result;
		
	}
	
	public function newContainerAction(Request $request, $page, $parent_id) {
		
		//initialize some shit
		$html = '';
		$blockManager = $this->container->get('brs.page.manager.block');
		
		//get the page
		$page = $this->getDoctrine()->getRepository('ApplicationSonataPageBundle:Page')->find($page);
		
		//assume the first child is the main block container...UPDATE
		$parent = $this->getDoctrine()->getRepository('ApplicationSonataPageBundle:Block')->find($parent_id);
		
		//create the new container
		$container = $blockManager->createContainerBlock($page);
		
		//set the number of cols
		$num_cols = $request->get('num_cols');
		
		//add the empty blocks
		for($i=0; $i<$num_cols; $i++) {
			$blockManager->addSelectionBlock($container);
		}
		
		//add the container to the global container
		$parent->addChildren($container);
        $container->setPosition(count($page->getBlocks()) + 1);
        $container->setEnabled(true);
		
		//save the new block
		$em = $this->getDoctrine()->getEntityManager();
		$em->persist($container);
		$em->persist($page);
		$em->flush();
		
		//render the template
		$html = $this->container->get('templating')->render($container->getSetting('template'), array(
            'block' => $container,
        ));
		
		//get the html code
		$result = array(
			'success' => true,
			'html' => $html,
			'id' => $container->getId(),
			'classes' => $container->getSetting('classes'),
		);
		
        return $result;
		
    }
	
	/**
	 * Get a list of content types.  Shitty hard coded for now to keep simple and flexible
	 */
	public function getContentTypes() {
		
		$contentManager = $this->container->get('brs.page.manager.block');
		
		return $contentManager->getTextBlockTypes();
		
	}
	
	public function getPublishAction($site_id) {
		
        $notification_m = $this->get('sonata.notification.backend');
		
		$result = $notification_m->createAndPublish('sonata.page.create_snapshots', array(
            'siteId' => $site_id,
            'mode'   => 'async'
        ));
			
	    $hostname = $this->getRequest()->getHost();
	    if (!empty($hostname)) {
			$app = new Application($this->container->get('kernel'));
			$app->setAutoExit(false);
			
			$path = $this->get('kernel')->getRootDir() . '/../web';
			
			$input = new StringInput("sonata:seo:sitemap $path $hostname");
			
			$output = new StreamOutput(fopen('php://temp', 'w'));
			
			$returnVal = $app->doRun($input, $output);
			
			rewind($output->getStream());
			$response =  stream_get_contents($output->getStream());
			
	    }
	    
		return array('success', $result);
		
	}
	
    /**
     * Get the list of pages
     *
     * @return array of pages
     *
     * @View()
     */
    public function cgetAction() {
    	
    	$pages = $this->getDoctrine()->getRepository('ApplicationSonataPageBundle:Page')->findAll();
		return $pages;
		
    }
	
    /**
     * Display the form
     *
     * @return Form form instance
     *
     * @View(template="BRSCoreBundle:REST:form.html.twig")
     */
    public function newAction() {
    	
		$page = new Page();
		
        $form = $this->getForm($page);
		
		return $form;
		
    }

    /**
     * Display the form
     *
     * @return Form form instance
     *
     * @View()
     */
    public function postAction(Request $request, $page) {
    	
		$page = $this->getDoctrine()->getRepository('ApplicationSonataPageBundle:Page')->find($page);
		
        $form = $this->getForm($page);
		
	    $form->handleRequest($request);
	    
		$page = $form->getData();
		
		$return = array(
			'success' => false,
		);
		
		if($form->isValid()) {
			
			try {
				
				$page = $form->getData();
				$em = $this->getDoctrine()->getManager();
				
				if($form->get('delete')->isClicked()) {
					
					$em->remove($page);
					$url = $this->container->get('kernel')->getEnvironment() == 'dev' ? '/app_dev.php' : '/';
					
				} else {
					
					$em->persist($page);
					
					$url = $_SERVER['HTTP_REFERER'];
					
					if(!$url && $this->container->get('kernel')->getEnvironment() == 'dev') {
						$url = '/app_dev.php';
					} elseif(!$url) {
						$url = '/';
					}
					
				}
				
				$em->flush();
				
				return $this->redirect($url);
				
			} catch (\Exception $e) {
				
				$return['message'] = $e->getMessage();
				    
			}
			
	    } else {
			
			$return['message'] = $form->getErrors();
			
		}
		
	    return $return;
		
    }
    
	/**
     * Display the form
     *
     * @return Form form instance
     *
     * 
	 * @View(template="BRSCoreBundle:REST:create_page.html.twig")
     */
	public function getAction($page_id) {
		
		$page = $this->getDoctrine()->getRepository('ApplicationSonataPageBundle:Page')->find($page_id);
		
        $form = $this->getForm($page);
		
		return $form;
		
	}
	
    private function createPage($page)
    {
	    return new Page();
    }
	
    protected function getForm($page)
    {
    	
		$route = $this->get('router')->generate('api_post_page', array('page' => $page->getId()));
		
        return $this->createForm(new PageType(), $page, array(
			'action' => $route,
			'attr' => array(
				'delete' => true,
			),
	    ));
    }
	
}