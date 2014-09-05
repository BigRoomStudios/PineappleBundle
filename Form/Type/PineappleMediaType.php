<?php

namespace BRS\PineappleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PineappleMediaType extends AbstractType
{
	
	public function __construct($container) {
		$this->container = $container;
	}
	
	public function buildView(FormView $view, FormInterface $form, array $options) {
		
		$media_id = $form->getData();
		
		if($media_id) {
			$media = $this->getMedia($media_id);
			$view->vars['media'] = $media;
		}
		
	}
	
	public function getMedia($media_id) {
		$media = $this->container->get('doctrine')->getManager()->getRepository('ApplicationSonataMediaBundle:Media')->find($media_id);
		return $media;
	}
	
	public function getParent() {
		return 'integer';
	}
	
	public function getName() {
		return 'pineapple_media';
	}
}