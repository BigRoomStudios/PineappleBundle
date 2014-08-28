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
		
		$categories = $blockManager->getPineappleCategories();
		
		$current = current($categories);
		
		$pineapples = $blockManager->getPineapples($current);
		
		// print('<pre>');
		// print_r($pineapples);
		// print_r($categories);
		// print_r($current);
		// die('</pre>');
		
		/*foreach($pineapples as $key => $pineapple) {
			$pineapples[$key]['transform'] = $this->container->get('router')->generate('api_post_widget_transform_widget', array(
				'block_id' => $blockContext->getSetting('block_id'),
				'blockServiceId' => $pineapple['id'],
			));
		}*/
		
		// print('<pre>');
		// print_r($blockContext->getSettings());
		// print_r($pineapples);
		// die('</pre>');
		
		return $this->renderResponse($blockContext->getTemplate(), array(
            'block'     => $blockContext->getBlock(),
            'settings'  => $blockContext->getSettings(),
            'pineapples' => $pineapples,
            'categories' => $categories,
            'current_category' => $current,
        ), $response);
		
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        /*$contextChoices = $this->getContextChoices();

        if (!$block->getSetting('mediaId') instanceof MediaInterface) {
            $this->load($block);
        }

        $formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array('required' => false)),
                array('context', 'choice', array('required' => true, 'choices' => $contextChoices)),
                array('format', 'choice', array('required' => count($formatChoices) > 0, 'choices' => $formatChoices)),
                array($this->getMediaBuilder($formMapper), null, array()),
            )
        ));*/
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Widget List Block';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'template' => 'BRSPineappleBundle:Blocks:widget_list.html.twig',
            'block_id' => null,
        ));
    }
	
    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {

    }
	
}
