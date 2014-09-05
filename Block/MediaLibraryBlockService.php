<?php

namespace BRS\PineappleBundle\Block;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MediaLibraryBlockService extends ListBlockService
{
	
	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'Media Library';
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function setDefaultSettings(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'template' => 'BRSPineappleBundle:Blocks:media-library.html.twig',
			'entity' => 'ApplicationSonataMediaBundle:Media',
		));
	}
	
}
