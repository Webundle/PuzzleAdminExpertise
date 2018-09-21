<?php

namespace Puzzle\Admin\ExpertiseBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class AbstractProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('title', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.project.title',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('service', HiddenType::class)
            ->add('description', TextareaType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.project.description',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control summernote']
            ])
            ->add('client', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.project.client',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('location', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.project.location',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('startedAt', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.project.startedAt',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control pickadate']
            ])
            ->add('endedAt', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.project.endedAt',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control pickadate']
            ])
            ->add('gallery', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.project.gallery',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
        ;
    }
}