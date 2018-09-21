<?php

namespace Puzzle\Admin\ExpertiseBundle\Form\Type;

use Puzzle\Admin\ExpertiseBundle\Form\Model\AbstractServiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class ServiceCreateType extends AbstractServiceType
{
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'service_create');
        $resolver->setDefault('validation_groups', ['Create']);
    }
    
    public function getBlockPrefix() {
        return 'admin_expertise_service_create';
    }
}