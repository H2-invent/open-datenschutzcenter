<?php

/**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Form\Type;

use App\Entity\VVTDatenkategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VVTDatenkategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextareaType::class, [
                'label' => 'dataCategoryName',
                'empty_data' => '',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'dataCategoryNameHelp'
            ])
            ->add('datenarten', TextareaType::class, [
                'label' => 'dataTypes',
                'empty_data' => '',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'dataTypesHelp'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VVTDatenkategorie::class,
        ]);
    }
}
