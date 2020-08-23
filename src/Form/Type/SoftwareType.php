<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Datenweitergabe;
use App\Entity\Software;
use App\Entity\VVT;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoftwareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, ['label' => 'Name der Software', 'required' => true, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'Software Nummer', 'required' => false, 'translation_domain' => 'form'])
            ->add('description', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Beschreibung der Software', 'required' => true, 'translation_domain' => 'form'])
            ->add('licenseType', ChoiceType::class, [
                'choices' => [
                    'Keine Angabe' => 0,
                    'Free and Open Source (FOSS)' => 10,
                    'Kostenlos/Closed Source' => 20,
                    'Benutzerlizenzen' => 30,
                    'Gerätelizenzen' => 40,
                    'Serverlizenzen' => 50,
                    'Managed Service' => 60,
                    'Misch-Lizenzen' => 70,
                    'Andere Lizenz' => 90,],
                'label' => 'Lizenz Typ',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('license', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Lizenzbeschreibung', 'required' => false, 'translation_domain' => 'form'])
            ->add('licenseExpiration', DateType::class, ['label' => 'Lizenzablaufsdatum', 'required' => false, 'translation_domain' => 'form'])
            ->add('reference', TextType::class, ['label' => 'Aktenzeichen', 'required' => false, 'translation_domain' => 'form'])
            ->add('vvts', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVT::class,
                'choices' => $options['processes'],
                'label' => 'Mit dieser Software verbundene Verarbeitungen *',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => true,
                'expanded' => true
            ])
            ->add('datenweitergabe', EntityType::class, [
                'choice_label' => 'gegenstand',
                'class' => Datenweitergabe::class,
                'choices' => $options['datenweitergabe'],
                'label' => 'Mit dieser Software verbundene Datenweitergaben',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => true,
                'expanded' => true
            ])
            ->add('purchase', DateType::class, ['label' => 'Kaufdatum/Anschaffungsdatum', 'required' => false, 'translation_domain' => 'form'])
            ->add('build', TextType::class, ['label' => 'Version', 'required' => true, 'translation_domain' => 'form'])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Angelegt' => 0,
                    'In Bearbeitung' => 10,
                    'In Prüfung' => 20,
                    'Zur Freigegebe vorgelegt' => 30,
                    'Inaktiv' => 60,],
                'label' => 'Status',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('location', TextType::class, ['label' => 'Standort der Software', 'required' => false, 'translation_domain' => 'form'])
            ->add('archiving', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Archivierungskonzept', 'required' => false, 'translation_domain' => 'form'])
            ->add('recovery', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Notfallplanung und Widerherstellungskonzept', 'required' => false, 'translation_domain' => 'form'])
            ->add('permissions', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Berechtigungskonzept', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Software::class,
            'processes' => array(),
            'datenweitergabe' => array(),
        ]);
    }
}
