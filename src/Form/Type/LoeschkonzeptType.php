<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Form\Type;

use App\Entity\Loeschkonzept;
use App\Entity\VVTDatenkategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class LoeschkonzeptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vvtdatenkategories', EntityType::class, [
                'class' => VVTDatenkategorie::class,
                'choices' => $options['vvtdatenkategories'],
                'label' => 'dataCategories',
                'multiple' => true, 
                'required' => false,
                'translation_domain' => 'form',
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true',
                ],
            ])
            
            ->add('standartlf', TextareaType::class, [
                'label' => 'standardDeleteDeadline',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'standardDeleteDeadline'
            ])
            ->add('loeschfrist', TextareaType::class, [
                'label' => 'legalDeleteDeadline',
                'required' => false,
                'translation_domain' => 'form',
                'help' => 'legalDeleteDeadlineHelp'
                ])
            ->add('speicherorte', TextareaType::class, [
                'label' => 'dataStorageLocations',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'dataStorageLocationsHelp'
                ])
            ->add('loeschbeauftragter', TextareaType::class, [
                'label' => 'deleteOfficial',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'deleteOfficialHelp'
                ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['rows' => 10],
                'label' => 'deleteDescription',
                'required' => false,
                'translation_domain' => 'form',
                'help' => 'deleteDescriptionHelp'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loeschkonzept::class,
            'vvtdatenkategories' => array(),
        ]);
    }
}
