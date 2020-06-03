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

        $builder
            ->add('beschreibung', TextareaType::class, ['attr' => ['rows' => 12],'label' => 'Systematische Beschreibung der geplanten Verarbeitungsvorgänge', 'required' => true, 'translation_domain' => 'form'])
            ->add('notwendigkeit', TextareaType::class, ['attr' => ['rows' => 12],'label' => 'Bewertung der Notwendigkeit', 'required' => true, 'translation_domain' => 'form'])
            ->add('risiko', TextareaType::class, ['attr' => ['rows' => 12],'label' => 'Bewerbtung der Risiken', 'required' => true, 'translation_domain' => 'form'])
            ->add('abhilfe', TextareaType::class, ['attr' => ['rows' => 12],'label' => 'Zur Bewältigung der Risiken geplante Anhilfemaßnahmen (Garantie, Sicherheitskopien,...)', 'required' => true, 'translation_domain' => 'form'])
            ->add('standpunkt', TextareaType::class, ['attr' => ['rows' => 12],'label' => 'Standpunkt von weiteren Organen (z.B. Betriebsrat)', 'required' => true, 'translation_domain' => 'form'])
            ->add('dsb', TextareaType::class, ['attr' => ['rows' => 12],'label' => 'Standpunkt des Datenschutzbeauftragten', 'required' => false, 'translation_domain' => 'form'])
            ->add('ergebnis', TextareaType::class, ['attr' => ['rows' => 12],'label' => 'Ergebnis', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VVTDsfa::class,
        ]);
    }
}
