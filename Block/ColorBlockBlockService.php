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
class ColorBlockBlockService extends base {
  
  public function buildEditForm(FormMapper $formMapper, BlockInterface $block) {
    
    $formMapper->add('settings', 'sonata_type_immutable_array', array(
      'label' => '',
      'keys' => array(
        array('color', 'text', array('required' => false, 'label' => 'Background color')),
        array('content', 'textarea', array(
          'attr' => array(
            'class' => 'tinymce',
            'id' => 'tinymce_'.$block->getId(),
          ),
        )),
      )
    ));
    
  }

  public function setDefaultSettings(OptionsResolverInterface $resolver) {
    
    parent::setDefaultSettings($resolver);
    
    $resolver->setDefaults(array(
      'template' => 'BRSPineappleBundle:Blocks:content.color-block.html.twig',
      'color' => '#3498db',
      'content' => '<h2>Editable color block section</h2>',
    ));
  }
  
}