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
                'label' => 'dpiaDescription.label',
                'required' => true,
                'help' => 'dpiaDescription.help',
                'translation_domain' => 'form'
            ])
            ->add('notwendigkeit', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'needAssessment.label',
                'required' => true,
                'help' => 'needAssessment.help',
                'translation_domain' => 'form'
            ])
            ->add('risiko', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'riskAssessment.label',
                'required' => true,
                'help' => 'riskAssessment.help',
                'translation_domain' => 'form'
            ])
            ->add('abhilfe', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'correctiveActions.label',
                'required' => true,
                'help' => 'correctiveActions.help',
                'translation_domain' => 'form'
            ])
            ->add('standpunkt', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'positionOtherInstitutions',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('dsb', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'positionDataProtectionOfficer',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('ergebnis', TextareaType::class, [
                'attr' => ['class' => $summernoteClass],
                'label' => 'dpiaResult',
                'required' => false,
                'translation_domain' => 'form'
            ])
            ->add('save', SubmitType::class, [
                'attr' => array('class' => 'btn btn-primary'),
                'label' => 'save',
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
