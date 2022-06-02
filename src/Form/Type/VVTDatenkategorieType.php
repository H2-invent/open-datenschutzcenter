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
                'label' => 'Name',
                'empty_data' => '',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'Der Name bezeichnet die Datenkategorie, unter der die dazugehörigen Datenarten zusammengefasst werden sollen.'
            ])
            ->add('datenarten', TextareaType::class, [
                'label' => 'Datenarten',
                'empty_data' => '',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'Hier sollen alle Datenarten eingetragen werden, die sich auf die oben benannte Datenkategorie beziehen.'
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
