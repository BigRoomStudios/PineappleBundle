<?php

namespace BigRoomStudios\PineappleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use BigRoomStudios\PineappleBundle\DependencyInjection\Compiler\WidgetServiceCompilerPass;

class BRSCoreBundle extends Bundle
{
	
	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerBuilder $container) {
		
		parent::build($container);
		
		$container->addCompilerPass(new WidgetServiceCompilerPass());
		
	}
	
}