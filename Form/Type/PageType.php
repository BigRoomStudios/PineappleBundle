<?php

namespace BRS\PineappleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

class PageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	
        $builder
            ->add('name', NULL, array(
                'label' => 'Page Name'
            ))
            ->add('title', NULL, array(
                'label' => 'Title'
            ))
			->add('parent', NULL, array(
                'label' => 'Parent Page'
            ))
			->add('type', 'sonata_page_type_choice')
			->add('templateCode', 'sonata_page_template', array(
                'label' => 'Template'
            ))
            /*->add('slug', 'text', array(
                'label' => 'Slug'
            ))
            ->add('customUrl', 'text', array(
                'label' => 'Custom URL (optional)',
                'attr' => array(
                	'required' => false,
				),
            ))
            ->add('parent', NULL, array(
                'label' => 'Parent Page'
            ))
            ->add('enabled', NULL, array(
                'label' => 'Enabled?'
            ))*/
            ->add('position', NULL, array(
                'label' => 'Order'
            ))
            ->add('metaKeyword', NULL, array(
                'label' => 'Meta Keywords'
            ))
            ->add('metaDescription', NULL, array(
                'label' => 'Meta Description'
            ))
            ->add('save', 'submit');
		
		//add in the delete button if necessary...I think this way of doing this is shit.
		if(isset($options['attr']['delete'])) {
			$builder->add('delete', 'submit');
		}
		
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\Sonata\PageBundle\Entity\Page'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'page';
    }
}
