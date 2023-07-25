<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Policies;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTPersonen;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PolicyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $summernoteClass = 'summernote';
        if ($options['disabled']) {
            $summernoteClass .= ' summernote-disable';
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'policyName',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('scope', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'policyScope',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('risk', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'policyPotentialDangers',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('foundation', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'policyLegislation',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('reference', TextType::class, [
                'label' => 'fileNumber',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('processes', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVT::class,
                'choices' => $options['processes'],
                'label' => 'affectedProcesses',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('protection', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'policySafetyMeasures',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('notes', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'policyTrainingOffer',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('consequences', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'policyNoncomplianceConsequences',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('contact', TextareaType::class, [
                'attr' => ['row' => 5],
                'label' => 'policyContacts',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('people', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTPersonen::class,
                'choices' => $options['personen'],
                'label' => 'affectedPersons',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('categories', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTDatenkategorie::class,
                'choices' => $options['kategorien'],
                'label' => 'affectedDataCategories',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('person', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'responsibilitiesForSafetyMeasures',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'created' => 0,
                    'inProgress' => 1,
                    'inReview' => 2,
                    'submitted' => 3,
                    'outdated' => 4,],
                'label' => 'status',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('uploadFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'delete_label' => 'delete',
                'label' => 'policyDocument',
                'translation_domain' => 'form',
                'download_label' => false
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'), 'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Policies::class,
            'personen' => array(),
            'kategorien' => array(),
            'user' => array(),
            'processes' => array()
        ]);
    }
}
