<?php

namespace Puzzle\Admin\ExpertiseBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 */
class AbstractFaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder
        ->add('question', TextType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.faq.question',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control'],
        ])
        ->add('answer', TextareaType::class, [
            'translation_domain' => 'admin',
            'label' => 'expertise.faq.answer',
            'label_attr' => ['class' => 'form-label'],
            'attr' => ['class' => 'form-control summernote', 'rows' => 8],
        ])
        ;
    }
}