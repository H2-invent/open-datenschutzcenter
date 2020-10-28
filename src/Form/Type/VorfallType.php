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
            ->add('gemeldet', CheckboxType::class, ['label' => 'Datenschutzvorfall der Landesdatenschutzbehörde gemeldet. Wenn ja, die meldende Person in getroffene Maßnahmen eintragen', 'required' => false, 'translation_domain' => 'form'])
            ->add('betroffeneGemeldet', CheckboxType::class, ['label' => 'Datenschutzvorfall an die betroffenen Personen gemeldet', 'required' => false, 'translation_domain' => 'form'])
            ->add('auftraggeberGemeldet', CheckboxType::class, ['label' => 'Datenschutzvorfall an die Auftraggeber gemeldet', 'required' => false, 'translation_domain' => 'form'])
            ->add('fakten', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Fakten zum Datenschutzvorfall', 'required' => true, 'translation_domain' => 'form'])
            ->add('auswirkung', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Auswirkungen und Folgen der Datenschutzverletzung', 'required' => true, 'translation_domain' => 'form'])
            ->add('massnahmen', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Getroffene Maßnahmen', 'required' => true, 'translation_domain' => 'form'])
            ->add('datum', DateTimeType::class, ['label' => 'Datenschutzvorfall bemerkt', 'required' => true, 'translation_domain' => 'form', 'widget' => 'single_text'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'), 'label' => 'Speichern', 'translation_domain' => 'form'])
            ->add('personen', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTPersonen::class,
                'choices' => $options['personen'],
                'label' => 'Betroffene Personenkategorien',
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
                'label' => 'Betroffene Datenkategorien',
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
