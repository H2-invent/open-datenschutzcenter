<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Vorfall;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTPersonen;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VorfallType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('gemeldet', CheckboxType::class, ['label' => 'incidentReportedToStateAgency', 'required' => false, 'translation_domain' => 'form'])
            ->add('betroffeneGemeldet', CheckboxType::class, ['label' => 'incidentReportedToAffectedPersons', 'required' => false, 'translation_domain' => 'form'])
            ->add('auftraggeberGemeldet', CheckboxType::class, ['label' => 'incidentReportedToClients', 'required' => false, 'translation_domain' => 'form'])
            ->add('fakten', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'incidentFacts', 'required' => true, 'translation_domain' => 'form'])
            ->add('auswirkung', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'incidentConsequences', 'required' => true, 'translation_domain' => 'form'])
            ->add('massnahmen', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'incidentMeasures', 'required' => true, 'translation_domain' => 'form'])
            ->add('datum', DateTimeType::class, ['label' => 'incidentDiscovered', 'required' => true, 'translation_domain' => 'form', 'widget' => 'single_text'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn'), 'label' => 'save', 'translation_domain' => 'form'])
            ->add('personen', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTPersonen::class,
                'choices' => $options['personen'],
                'label' => 'affectedPersons',
                'translation_domain' => 'form',
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('daten', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTDatenkategorie::class,
                'choices' => $options['daten'],
                'label' => 'affectedDataCategories',
                'translation_domain' => 'form',
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vorfall::class,
            'daten' => array(),
            'personen' => array()
        ]);
    }
}
