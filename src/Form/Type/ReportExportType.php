<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class, ['label' => 'Titel', 'required' => true, 'translation_domain' => 'form'])
            ->add('von', DateType::class, ['label' => 'Von', 'format' => 'dd.MM.yyyy', 'required' => true, 'translation_domain' => 'form'])
            ->add('bis', DateType::class, ['label' => 'Bis', 'format' => 'dd.MM.yyyy', 'required' => true, 'translation_domain' => 'form'])
            ->add('kontakt', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'Bearbeiter',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false
            ])
            ->add('report', CheckboxType::class, ['label' => 'Nur Aktivitäten für offiziellen Tätigkeitsbericht beinhalten', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary reportBtn'), 'label' => 'Weiter', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => array()
        ]);
    }
}
