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
class HeaderSplashBlockService extends base {
  
  public function setDefaultSettings(OptionsResolverInterface $resolver) {
    
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block) {
    
    $formMapper->add('settings', 'sonata_type_immutable_array', array(
      'label' => '',
      'keys' => array(
        array('content', 'textarea', array(
          'attr' => array(
            'class' => 'tinymce',
            'id' => 'tinymce_'.$block->getId(),
          ),
        )),
      )
    ));
    
  }

    parent::setDefaultSettings($resolver);
    
    $resolver->setDefaults(array(
      'template' => 'BRSPineappleBundle:Blocks:header.splash.html.twig',
      'content' => '<h1>Welcome to the index</h1><p>Cras justo odio, dapibus ac facilisis in, egestas eget quam. Donec id elit non mi porta gravida at eget metus. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>',
    ));
  }
  
}

?>