<?php

namespace Puzzle\Admin\ExpertiseBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 */
class AbstractPartnerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
        ->add('name', TextType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.partner.name',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control'],
        ])
        ->add('location', TextType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.partner.location',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control'],
        ])
        ;
    }
}