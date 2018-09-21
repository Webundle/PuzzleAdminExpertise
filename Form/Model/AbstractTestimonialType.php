<?php

namespace Puzzle\Admin\ExpertiseBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 */
class AbstractTestimonialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
        ->add('author', TextType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.testimonial.author',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control'],
        ])
        ->add('company', TextType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.testimonial.company',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control'],
        ])
        ->add('position', TextType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.testimonial.position',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control'],
        ])
        ->add('message', TextareaType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.testimonial.message',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control', 'rows' => 8],
        ])
        ;
    }
}