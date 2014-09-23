<?php

namespace BRS\PineappleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

class SiteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        	->add('name', 'text', array(
                'label' => 'Name',
                'required' => false,
            ))
            ->add('title', 'text', array(
                'label' => 'Title',
                'required' => false,
            ))
			->add('gaCode', 'text', array(
                'label' => 'Google Analytics Code',
                'required' => false,
            ))
			->add('gaUrl', 'text', array(
                'label' => 'Google Analytics URL',
                'required' => false,
            ))
            ->add('metaKeywords', NULL, array(
                'label' => 'Meta Keywords',
                'required' => false,
            ))
            ->add('metaDescription', NULL, array(
                'label' => 'Meta Description',
                'required' => false,
            ))
            ->add('save', 'submit')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\Sonata\PageBundle\Entity\Site'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'site';
    }
}
