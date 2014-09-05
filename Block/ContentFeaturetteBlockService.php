<?php

namespace BRS\PineappleBundle\Block;

use Sonata\PageBundle\Block\ChildrenPagesBlockService as base;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 
 */
class ContentFeaturetteBlockService extends base {
	
	/**
	 * {@inheritdoc}
	 */
	public function buildEditForm(FormMapper $formMapper, BlockInterface $block) {
		
		$formMapper->add('settings', 'sonata_type_immutable_array', array(
			'label' => false,
			'keys' => array(
				array('flip', 'choice', array(
					'choices'   => array('n' => 'No', 'y' => 'Yes'),
					'required'  => true,
					'label' => 'Flip layout?'
				)),
				array('content', 'textarea', array(
					'label' => false,
					'attr' => array(
						'class' => 'tinymce',
						'id' => 'tinymce_'.$block->getId(),
					),
				)),
				array('media', 'sonata_media_type', array(
					'provider' => 'sonata.media.provider.image',
					'context' => 'default',
					'required' => false,
					'label' => false,
				)),
			)
		));
		
    }
	
	public function setDefaultSettings(OptionsResolverInterface $resolver) {
		
		parent::setDefaultSettings($resolver);
		
		$resolver->setDefaults(array(
			'template' => 'BRSPineappleBundle:Blocks:content.featurette.html.twig',
			'content' => '<h2 class="featurette-heading">First featurette heading. <span class="text-muted">It will blow your mind.</span></h2><p class="lead">Donec ullamcorper nulla non metus auctor fringilla. Vestibulum id ligula porta felis euismod semper. Praesent commodo cursus magna, vel scelerisque nisl consectetur. Fusce dapibus, tellus ac cursus commodo.</p>',
			'flip' => 'n',
			'media'    => null,
		));
		
	}
	
}