<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AuditTom;
use App\Entity\AuditTomAbteilung;
use App\Entity\AuditTomStatus;
use App\Entity\AuditTomZiele;
use App\Entity\Team;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuditTomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('frage', TextType::class, ['label' => 'Fragestellung', 'required' => true, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'AuditTom Nummer', 'required' => true, 'translation_domain' => 'form'])
            ->add('bemerkung', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Bemerkung', 'required' => true, 'translation_domain' => 'form'])
            ->add('empfehlung', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Empfehlung', 'required' => true, 'translation_domain' => 'form'])
            ->add('ziele', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomZiele::class,
                'choices' => $options['ziele'],
                'label'=>'Schutzziele',
                'translation_domain' => 'form',
                'multiple' =>true,
                'expanded' => true,
            ])
            ->add('abteilung', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomAbteilung::class,
                'choices' => $options['abteilungen'],
                'label'=>'Abteilungen',
                'translation_domain' => 'form',
                'multiple' =>true,
                'expanded' => true,
            ])
            ->add('status', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomStatus::class,
                'choices' => $options['status'],
                'label'=>'Status',
                'translation_domain' => 'form',
                'multiple' =>false,
            ])
            ->add('tomAttribut', TextType::class, ['label' => 'Attribut für die globale TOM', 'required' => false, 'translation_domain' => 'form'])
            ->add('tomZiel', ChoiceType::class, [
                'choices'  => [
                    'Bitte auswählen (Nicht in der TOM darstellen)' => null,
                    'Pseudonymisierung Verschlüsselung' => 1,
                    'Zutrittskontrolle' => 2,
                    'Zugangskontrolle' => 3,
                    'Zugriffskontrolle' => 4,
                    'Benutzerkontrolle' => 5,
                    'Speicherkontrolle' => 6,
                    'Trennbarkeit' => 7,
                    'Datenintegrität' => 8,
                    'Transportkontrolle ' => 9,
                    'Übertragungskontrolle' => 10,
                    'Eingabekontrolle' => 11,
                    'Zuverlässigkeit' => 12,
                    'Auftragskontrolle' => 13,
                    'Verfügbarkeitskontrolle' => 14,
                    'Wiederherstellbarkeit' => 15,
                    'Evaluierung' => 16,
                    ],
                'label'=>'TOM Possition des Attributes',
                'translation_domain' => 'form',
                'required' => false,
                'multiple' =>false,
            ])
            ->add('kategorie', ChoiceType::class, [
                'choices'  => [
                    'Bitte auswählen' => '',
                    'Datenschutzmanagement' => 'Datenschutzmanagement',
                    'Verbindliche Vorlagen Datenschutz' => 'Verbindliche Vorlagen Datenschutz',
                    'Arbeitsanweisungen' => 'Arbeitsanweisungen',
                    'Notfallplanung und Dokumentation' => 'Notfallplanung und Dokumentation',
                    'Bauliche Sicherheit' => 'Bauliche Sicherheit',
                    'Video' => 'Video',
                    'Authentifizierungen' => 'Authentifizierungen',
                    'Berechtigungen' => 'Berechtigungen',
                    'Logs' => 'Logs',
                    'Backups' => 'Backups',
                    'Datenvernichtung/ Löschen' => 'Datenvernichtung/ Löschen',
                    'Übermittlung/ Transport' => 'Übermittlung/ Transport',
                    'Fernwartung' => 'Fernwartung',
                    'Mobile Geräte' => 'Mobile Geräte',
                    'WLAN' => 'WLAN',
                    'Marketing' => 'Marketing',
                    'Cloud' => 'Cloud',
                    'Archiv' => 'Archiv',
                    'IT Sicherheit' => 'IT Sicherheit',
                    'MFC' => 'MFC',
                    'Serverraum' => 'Serverraum',
                    'Mandantenfähigkeit' => 'Mandantenfähigkeit',
                    'Compliance' => 'Compliance',
                    'GoBD' => 'GoBD',
                    'Sonstiges' => 'Sonstiges',
                ],
                'label'=>'Kategorie',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' =>false,
            ])

            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AuditTom::class,
            'ziele' => array(),
            'abteilungen' => array(),
            'status' => array(),
        ]);
    }
}
