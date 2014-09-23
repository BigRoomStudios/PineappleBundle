<?php

namespace BRS\PineappleBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PineappleWidgetListBlockService extends BaseBlockService
{
	/**
     * @param string                                                    $name
     * @param \Symfony\Component\Templating\EngineInterface             $templating
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Sonata\MediaBundle\Model\MediaManagerInterface           $mediaManager
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container) {
    	
        parent::__construct($name, $templating);
		$this->container    = $container;
		
    }
	
    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null) {
    	
		$blockManager = $this->container->get('brs.page.manager.block');
		
		//init some category vars
		$category = $blockContext->getSetting('category');
		$categories = $blockManager->getPineappleCategories();
		
		//if category not set in the settings, get the first category
		if(!$category) {
			$category = current($categories);
		}
		
		//get the pineapples for the given category
		$pineapples = $blockManager->getPineapples($category);
		
		return $this->renderResponse($blockContext->getTemplate(), array(
            'block'     => $blockContext->getBlock(),
            'settings'  => $blockContext->getSettings(),
            'pineapples' => $pineapples,
            'categories' => $categories,
            'current_category' => $category,
        ), $response);
		
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'Widget List Block';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'BRSPineappleBundle:Blocks:widget_list.html.twig',
            'category' => null,
        ));
    }
	
    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {

    }
	
}
