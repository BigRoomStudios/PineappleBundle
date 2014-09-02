<?php

namespace BRS\PineappleBundle\Block;

use Sonata\PageBundle\Block\ChildrenPagesBlockService as base;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * 
 */
class HeaderSplashBlockService extends base {
  
  public function setDefaultSettings(OptionsResolverInterface $resolver) {
    
    parent::setDefaultSettings($resolver);
    
    $resolver->setDefaults(array(
      'template' => 'BRSPineappleBundle:Blocks:header.splash.html.twig'
    ));
  }
  
}

?>