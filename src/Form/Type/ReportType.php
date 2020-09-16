<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Report;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('description', TextareaType::class, ['label' => 'Beschreibung', 'required' => true, 'translation_domain' => 'form'])
            ->add('user', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'User',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => true
            ])
            ->add('date', DateType::class, ['label' => 'Datum', 'format' => 'dd.MM.yyyy', 'required' => true, 'translation_domain' => 'form'])
            ->add('start', TimeType::class, ['label' => 'Startzeit', 'required' => true, 'translation_domain' => 'form'])
            ->add('end', TimeType::class, ['label' => 'Endzeit', 'required' => true, 'translation_domain' => 'form'])
            ->add('calcTime', TextType::class, ['label' => 'Abrechenbare Zeit in Minuten', 'required' => true, 'translation_domain' => 'form'])
            ->add('invoice', CheckboxType::class, ['label' => 'Abgerechnet', 'required' => false, 'translation_domain' => 'form'])
            ->add('inReport', CheckboxType::class, ['label' => 'Im Report anzeigen', 'required' => false, 'translation_domain' => 'form'])
            ->add('onsite', CheckboxType::class, ['label' => 'Vor Ort', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block mt-3'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
            'user' => array(),
        ]);
    }
}
