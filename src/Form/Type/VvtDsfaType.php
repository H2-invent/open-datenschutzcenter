<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\VVTDsfa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VvtDsfaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $summernoteClass = 'summernote';
        if ($options['disabled']) {
            $summernoteClass .= ' summernote-disable';
        }

        $builder
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'Systematische Beschreibung der geplanten Verarbeitungsvorgänge',
                'required' => true,
                'help' => 'Welche Verarbeitung ist geplant?, Welche Zuständigkeiten bestehen für die Verarbeitung?, Gibt es Normen oder Standards für die Verarbeitung?, Welche Daten werden verarbeitetet?, Wie verläuft der Lebenszyklus von Daten und Prozessen?, Mit Hilfe welcher Betriebsmittel erfolgt die Datenverarbeitung?',
                'translation_domain' => 'form'
            ])
            ->add('notwendigkeit', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'Bewertung der Notwendigkeit',
                'required' => true,
                'help' => 'Sind die Verarbeitungszwecke eindeutig definiert und rechtmäßig?, Aufgrund welcher Rechtsgrundlage erfolgt die Verarbeitung?, Sind die erhobenen Daten erforderlich, relevant und auf das für die Datenverarbeitung Notwendige beschränkt?, Sind die Daten korrekt und auf dem neuesten Stand?',
                'translation_domain' => 'form'
            ])
            ->add('risiko', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'Bewertung der Risiken',
                'required' => true,
                'help' => 'Was sind die Hauptbedrohungen, die zu dem Risiko führen könnten?, Was sind die Risikoquellen?, Was könnten die wesentlichen Auswirkungen für die betroffenen Personen sein, wenn das Risiko eintritt?',
                'translation_domain' => 'form'
            ])
            ->add('abhilfe', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'Zur Bewältigung der Risiken geplante Abhilfemaßnahmen (Garantie, Sicherheitskopien,...)',
                'required' => true,
                'help' => 'Datentrennung, Datensicherung, Verschlüsselung, Anonymisierung, Datentrennung, Zugangskontrolle, Zugriffskontrolle, Protokollierung, Archivierung, Datenminimierung, Betriebssicherheit, Papierdokumentensicherung',
                'translation_domain' => 'form'
            ])
            ->add('standpunkt', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'Standpunkt von weiteren Organen (z.B. Betriebsrat)',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('dsb', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'Standpunkt des Datenschutzbeauftragten',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('ergebnis', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'Ergebnis der Datenschutz-Folgenabschätzung',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('save', SubmitType::class, [
                'attr' => array('class' => 'btn btn-primary'),
                'label' => 'Speichern',
                'translation_domain' => 'form'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VVTDsfa::class,
        ]);
    }
}
