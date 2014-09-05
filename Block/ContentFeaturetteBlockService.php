<?php

namespace BRS\PineappleBundle\Block;

use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * 
 */
class ContentFeaturetteBlockService extends BaseBlockService {
	
	public function __construct($name, EngineInterface $templating, $media_manager) {
		parent::__construct($name, $templating);
		$this->media_manager = $media_manager;
	}
	
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
				array('media', 'pineapple_media', array(
					'required' => false,
					'label' => 'Media',
				)),
				array('media_title', 'text', array(
					'required' => false,
					'label' => 'Title',
				)),
				array('media_alt', 'text', array(
					'required' => false,
					'label' => 'Alt',
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
			'media_title' => null,
			'media_alt' => null,
		));
		
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function load(BlockInterface $block) {
		
		$media = $block->getSetting('media', null);
		
		if ($media) {
			$media = $this->media_manager->findOneBy(array('id' => $media));
		}
		
		$block->setSetting('media', $media);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function prePersist(BlockInterface $block) {
		$block->setSetting('media', is_object($block->getSetting('media')) ? $block->getSetting('media')->getId() : null);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function preUpdate(BlockInterface $block) {
		$block->setSetting('media', is_object($block->getSetting('media')) ? $block->getSetting('media')->getId() : null);
	}
	
	/**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {

    }
	
}