<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AuditTomAbteilung;
use App\Entity\Datenweitergabe;
use App\Entity\Produkte;
use App\Entity\Tom;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VVTType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('nummer', TextType::class, ['label' => 'Nummer der Verarbeitung', 'required' => true, 'translation_domain' => 'form'])
            ->add('name', TextType::class, ['label' => 'Bezeichung der Verarbeitung', 'required' => true, 'translation_domain' => 'form'])
            ->add('verantwortlich', TextareaType::class, ['label' => 'Verantwortliche Person (weitere)', 'required' => false, 'translation_domain' => 'form'])
            ->add('userContract', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'Verantwortliche Person intern',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => true
            ])
            ->add('zweck', TextareaType::class, ['attr' => ['rows' => 12], 'label' => 'Zweck der Verarbeitung', 'required' => true, 'translation_domain' => 'form'])
            ->add('jointControl', CheckboxType::class, ['label' => 'Handelt es sich um Joint Control (gem. Verarbeitung)', 'required' => false, 'translation_domain' => 'form'])
            ->add('auftragsverarbeitung', CheckboxType::class, ['label' => 'Handelt es sich um eine Auftragsverarbeitung', 'required' => false, 'translation_domain' => 'form'])
            ->add('speicherung', TextareaType::class, ['label' => 'Wo werden die Daten gespeichert', 'required' => true, 'translation_domain' => 'form'])
            ->add('loeschfrist', TextType::class, ['label' => 'Löschfristen', 'required' => true, 'translation_domain' => 'form'])
            ->add('weitergabe', TextareaType::class, ['label' => 'Weitergabe der Daten', 'required' => false, 'translation_domain' => 'form'])
            ->add('grundlage', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTGrundlage::class,
                'choices' => $options['grundlage'],
                'label' => 'Beschreiben Sie, weshalb die Datenverarbeitung erforderlich ist? (Zweck und Grundlage) *',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                'required' => true
            ])
            ->add('personengruppen', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTPersonen::class,
                'choices' => $options['personen'],
                'label' => 'Die Daten welcher Personen werden verarbeitet? *',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('datenweitergaben', EntityType::class, [
                'choice_label' => 'gegenstand',
                'class' => Datenweitergabe::class,
                'choices' => $options['daten'],
                'label' => 'Welche Datenweitergaben werden dieser Verarbeitung zuordnen?',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'expanded' => true,
            ])
            ->add('kategorien', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTDatenkategorie::class,
                'choices' => $options['kategorien'],
                'label' => 'Welche Daten(kategorien) werden verarbeitet? *',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('eu', CheckboxType::class, ['label' => 'Ja, Daten werden außerhalb der EU verarbeitet', 'required' => false, 'translation_domain' => 'form'])
            ->add('tomLink', EntityType::class, [
                'choice_label' => 'titel',
                'class' => Tom::class,
                'choices' => $options['tom'],
                'label' => 'Welche TOM wird für diese Verarbeitung verwendet?',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false
            ])
            ->add('tom', TextareaType::class, ['attr' => ['rows' => 12], 'label' => 'Weitere Hinweise zur TOM', 'required' => false, 'translation_domain' => 'form'])
            ->add('risiko', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTRisiken::class,
                'choices' => $options['risiken'],
                'label' => 'Mögliche Risko-Quellen',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('status', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTStatus::class,
                'choices' => $options['status'],
                'label' => 'Status',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('abteilung', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomAbteilung::class,
                'choices' => $options['abteilung'],
                'label' => 'Zugeordnete Abteilung',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false
            ])
            ->add('informationspflicht', TextareaType::class, ['attr' => ['rows' => 6], 'label' => 'Informationspflicht', 'required' => false, 'translation_domain' => 'form'])
            ->add('dsb', TextareaType::class, ['attr' => ['rows' => 6], 'label' => 'Kommentar des Datenschutzbeauftragten', 'required' => false, 'translation_domain' => 'form'])
            ->add('beurteilungEintritt', ChoiceType::class, [
                'choices' => [
                    'Bitte auswählen' => '',
                    'Vernachlässigbar' => 'Vernachlässigbar',
                    'Eingeschränk möglich' => 'Eingeschränk möglich',
                    'Signifikant' => 'Signifikant',
                    'Sehr wahrscheinlich' => 'Sehr wahrscheinlich',
                ],
                'label' => 'Risiko: Eintrittswahrscheinlichkeit',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' => false,
            ])
            ->add('beurteilungSchaden', ChoiceType::class, [
                'choices' => [
                    'Bitte auswählen' => '',
                    'Gering(kaum Auswirkung)' => 'Gering(kaum Auswirkung)',
                    'Eingeschränkt vorhanden' => 'Eingeschränkt vorhanden',
                    'Signifikant' => 'Signifikant',
                    'Hoch (schwerwiegend bis existenzbedrohend)' => 'Hoch (schwerwiegend bis existenzbedrohend)',
                ],
                'label' => 'Risiko: Schadenspotenzial',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' => false,
            ])
            ->add('produkt', EntityType::class, [
                'choice_label' => 'name',
                'class' => Produkte::class,
                'choices' => $options['produkte'],
                'label' => 'Zugeordnete Produkte',
                'help' => 'Mit "STRG können mehrere Produkte ausgewählt werden',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VVT::class,
            'grundlage' => array(),
            'personen' => array(),
            'kategorien' => array(),
            'risiken' => array(),
            'status' => array(),
            'user' => array(),
            'daten' => array(),
            'tom' => array(),
            'abteilung' => array(),
            'produkte' => array()
        ]);
    }
}
