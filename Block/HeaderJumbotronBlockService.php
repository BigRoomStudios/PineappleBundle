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
class HeaderJumbotronBlockService extends base {
  
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

  public function setDefaultSettings(OptionsResolverInterface $resolver) {
    
    parent::setDefaultSettings($resolver);
    
    $resolver->setDefaults(array(
      'template' => 'BRSPineappleBundle:Blocks:header.jumbotron.html.twig',
      'content' => '<h1>Jumbotron heading</h1><p class="lead">Cras justo odio, dapibus ac facilisis in, egestas eget quam. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>',
    ));
  }
  
}
