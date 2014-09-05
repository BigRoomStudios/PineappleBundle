<?php

namespace BRS\PineappleBundle\Block;

use Sonata\BlockBundle\Block\Service\TextBlockService as BaseBlock;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 
 */
class TextBlockService extends BaseBlock
{
	
	/**
	 * {@inheritdoc}
	 */
	public function buildEditForm(FormMapper $formMapper, BlockInterface $block) {
		
		$formMapper->add('settings', 'sonata_type_immutable_array', array(
			'label' => false,
			'keys' => array(
				array('content', 'textarea', array(
					'label' => null,
					'attr' => array(
						'class' => 'tinymce',
						'id' => 'tinymce_'.$block->getId(),
					),
				)),
			)
		));
		
    }
	
    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        	'content'  => '<h3>Text block title</h3><p>Dummy text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer sagittis quis urna non tincidunt. Donec tristique id metus eget sodales. Curabitur congue rhoncus lobortis. Nunc in justo metus. Integer iaculis nunc ut sodales pharetra. Sed vel eros massa.</p>',
            'template' => 'BRSPineappleBundle:Blocks:text.html.twig',
        ));
    }
	
}
