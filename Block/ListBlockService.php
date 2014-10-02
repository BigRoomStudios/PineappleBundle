<?php

namespace BRS\PineappleBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ListBlockService extends BaseBlockService
{
	
	/**
     * @param string                                                    $name
     * @param \Symfony\Component\Templating\EngineInterface             $templating
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param \Sonata\MediaBundle\Model\MediaManagerInterface           $mediaManager
     */
    public function __construct($name, EngineInterface $templating, ContainerInterface $container) {
    	
        parent::__construct($name, $templating);
		$this->container    = $container;
		
    }
	
	/**
	 * {@inheritdoc}
	 */
	public function execute(BlockContextInterface $blockContext, Response $response = null) {
		
		$settings = $blockContext->getBlock()->getSettings();
		
		$list = $this->getList($settings);
		
		return $this->renderResponse($blockContext->getTemplate(), array(
			'block' => $blockContext->getBlock(),
			'settings' => $settings,
			'list' => $list,
			//'pagination' => $this->pagination
		));
		
	}
	
	protected function getList($settings) {
		
		$qb = $this->getQueryBuilder($settings);
		
		$this->setFilters($qb, $settings);
		
		$this->setOrderBy($qb, $settings);
		
		$paginator = $this->getPaginator($qb, $settings);
		
		return $paginator;
		
		
		
		$query = $this->getBaseQuery($settings);
		
		if($settings['max_per_page']) {
			
			$list = $this->getPage($query, $settings);
			
		} else {
			
			$this->pagination = null;
			$list = $query->select('i')->getQuery()->getResult();
			
		}
		
		return $list;
		
	}
	
	protected function getQueryBuilder($settings) {
		
		$em = $this->container->get('doctrine')->getManager();
		
		$qb = $em->createQueryBuilder()
				 ->select('e')
				 ->from($settings['entity'], 'e');
		
		return $qb;
		
	}
	
	protected function getPaginator($qb, $settings) {
		
		//get the current page
		$request = $this->container->get('request');
		$this->page = $request->get('page') ? $request->get('page') : 1;
		
		//set the page on the query builder
		$qb->setFirstResult(($this->page-1) * $settings['max_per_page']);
		$qb->setMaxResults($settings['max_per_page']);
		
		//instantiate the paginator and return it
		$paginator = new Paginator($qb, true);
		return $paginator;
		
	}
	
	protected function setFilters($qb, $settings) {
		//hook for subclasses - may tie into settings in the future
	}
	
	protected function setOrderBy($qb, $settings) {
		
		if($settings['orderby']) {
			$qb->orderBy("e.{$settings['orderby']}");
		}
		
	}
	
    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block) {
        
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
			'label' => '',
			'keys' => array(
			    array('entity', 'text', array('required' => true, 'label' => 'Entity')),
			    array('template', 'text', array('required' => true, 'label' => 'Template')),
			    array('max_per_page', 'integer', array('required' => false, 'label' => 'Results Per Page')),
			)
		));
		
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'List Block';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultSettings(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'template' => 'BRSPineappleBundle:Blocks:list.html.twig',
            'entity' => null,
            'orderby' => array(),
            'max_per_page' => 20,
        ));
    }
	
    /**
     * {@inheritdoc}
     */
    public function validateBlock(ErrorElement $errorElement, BlockInterface $block) {
		
	}
	
}
