<?php

namespace Puzzle\Admin\ExpertiseBundle\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class AbstractStaffType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.staff.fullName',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.staff.description',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'required' => false
            ])
            ->add('position', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.staff.position',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('ranking', IntegerType::class, [
                'translation_domain' => 'admin',
                'label' => 'expertise.staff.ranking',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control']
            ])
            ->add('contacts', TextType::class, array(
                'translation_domain' => 'admin',
                'label' => 'expertise.staff.contacts',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => "tokenfield"],
                'required' => false
            ))
        ;
    }
}
