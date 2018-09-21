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
class AbstractServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.service.name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('parent', HiddenType::class)
            ->add('description', TextareaType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.service.description',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control summernote']
            ])
            ->add('classIcon', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.service.classIcon',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('ranking', IntegerType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.service.ranking',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
        ;
    }
}