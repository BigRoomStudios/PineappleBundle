<?php

namespace BRS\PineappleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use BRS\PineappleBundle\DependencyInjection\Compiler\PineappleWidgetsCompilerPass;

class BRSPineappleBundle extends Bundle
{
	
	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerBuilder $container) {
		
		parent::build($container);
		
		$container->addCompilerPass(new PineappleWidgetsCompilerPass());
		
	}
	
}