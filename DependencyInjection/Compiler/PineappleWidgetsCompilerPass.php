<?php

namespace BRS\PineappleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Inject page services into page service manager
 *
 * @author Olivier Paradis <paradis@ekino.com>
 */
class PineappleWidgetsCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    protected $manager = 'brs.page.manager.block';

    /**
     * @var string
     */
    protected $tagName = 'brs.pineapple_widget';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
    	
        if (!$container->hasDefinition($this->manager)) {
            return;
        }
		
        $definition = $container->getDefinition($this->manager);

        $taggedServices = $container->findTaggedServiceIds($this->tagName);
		
        foreach ($taggedServices as $id => $attributes) {
        	
			$parameters = array('id' => $id);
			
			$parameters = array_merge($parameters, current($attributes));
			
            $definition->addMethodCall('addPineapple', array($id, $parameters));
			
        }
		
		// print('<pre>');
		// print_r($taggedServices);
		// print('</pre>');
		
    }
}
