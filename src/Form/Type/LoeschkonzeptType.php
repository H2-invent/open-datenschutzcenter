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
                'label' => 'Datenkategorien',
                'multiple' => true, 
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true',
                ],
            ])
            
            ->add('standartlf', TextareaType::class, [
                'label' => 'Standard Löschfrist',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'Hier wird die Löschfrist eingetragen, die unternehmensintern für die ausgewählten Datenkategorien festgelegt wurde. Sie kann kürzer sein, als es das Gesetz fordert, darf die gesetzlichen Frist aber nicht überschreiten. Gibt es eine gesetzliche Mindest-Speicherdauer, darf die Standard Löschfrsit diese nicht unterschreiten. Existiert keine gesetzliche Vorgabe wird unternehmensintern eine Löschfrist festgelegt. Diese Löschfrist wird auch an anderen Stellen angezeigt.'
            ])
            ->add('loeschfrist', TextareaType::class, [
                'label' => 'gesetzliche Löschfrist',
                'required' => false,
                'translation_domain' => 'form',
                'help' => 'Sollte es eine gesetzliche Grundlage zur maximalen oder minimalen Speicherdauer geben, ist diese maximale oder minimale Löschfrist hier einzutragen.'
                ])
            ->add('speicherorte', TextareaType::class, [
                'label' => 'Speicherorte',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'Hier sind alle Orte, an denen Daten der betroffenen Kategorien gespeichert sind, anzugeben. Auch Auftragsverarbeiter können hier als Speicherort mit eingetragen werden.'
                ])
            ->add('loeschbeauftragter', TextareaType::class, [
                'label' => 'Löschbeauftragter',
                'required' => true,
                'translation_domain' => 'form',
                'help' => 'Hier kann eine Löschbeauftragter namentlich benannt oder eine Gruppe/Abteilung als Verantwortlicher eingetragen werden'
                ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['rows' => 10],
                'label' => 'Beschreibung',
                'required' => false,
                'translation_domain' => 'form',
                'help' => 'Die Beschreibung soll zur Dokumentation von Löschabläufen, Frist-Begründungen, Ausnahmen, Spezialfällen und weiteren Informationsdokumentationen dienen.'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Loeschkonzept::class,
        ]);
    }
}
