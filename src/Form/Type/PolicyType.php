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

        $builder
            ->add('title', TextType::class, ['label' => 'Name der Richtlinie', 'required' => true, 'translation_domain' => 'form'])
            ->add('scope', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Festlegung des Geltungsbereiches', 'required' => true, 'translation_domain' => 'form'])
            ->add('risk', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Abzuwehrende IT-Risiken, bestehende Gefahren und mögliche Konsequenzen (wichtig für die Motivation!)', 'required' => true, 'translation_domain' => 'form'])
            ->add('foundation', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Bezug zu Gesetzen, Verordnungen und Standards', 'required' => true, 'translation_domain' => 'form'])
            ->add('reference', TextType::class, ['label' => 'Aktenzeichen', 'required' => false, 'translation_domain' => 'form'])
            ->add('processes', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVT::class,
                'choices' => $options['processes'],
                'label' => 'Betroffene Arbeitsvorgänge und Fachverfahren',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('protection', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Zu ergreifende Schutzmaßnahmen in kurzer, verständlicher Form', 'required' => false, 'translation_domain' => 'form'])
            ->add('notes', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Hinweis auf Schulungsangebotet', 'required' => false, 'translation_domain' => 'form'])
            ->add('consequences', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Konsequenzen bei Nichtbeachtung der Sicherheitsrichtlinie', 'required' => false, 'translation_domain' => 'form'])
            ->add('contact', TextareaType::class, ['attr' => ['row' => 5], 'label' => 'Kontaktdaten von IT-Sicherheitsverantwortlichen und Datenschutzbeauftragten', 'required' => false, 'translation_domain' => 'form'])
            ->add('people', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTPersonen::class,
                'choices' => $options['personen'],
                'label' => 'Die Daten welcher Personen werden verarbeitet?',
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
                'label' => 'Betroffene Datenkategorien sind betroffen',
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
                'label' => 'Konkrete Verantwortlichkeiten für die Schutzmaßnahmen',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Angelegt' => 0,
                    'In Bearbeitung' => 1,
                    'Prüfung' => 2,
                    'Zur Freigabe vorgelegt' => 3,
                    'Veraltet' => 4,],
                'label' => 'Status',
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
                'delete_label' => 'Löschen',
                'label' => 'Dokument zur Richtlinie hochladen',
                'translation_domain' => 'form',
                'download_label' => false
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'), 'label' => 'Speichern', 'translation_domain' => 'form']);
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
