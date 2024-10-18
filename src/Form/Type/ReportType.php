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
            ->add('description', TextareaType::class, ['attr' => ['rows' => 12], 'label' => 'description', 'required' => true, 'translation_domain' => 'form'])
            ->add('user', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'user',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('date', DateType::class, ['label' => 'date', 'widget' => 'single_text', 'required' => true, 'translation_domain' => 'form'])
            ->add('start', TimeType::class, ['label' => 'startTime', 'required' => true, 'translation_domain' => 'form'])
            ->add('end', TimeType::class, ['label' => 'endTime', 'required' => true, 'translation_domain' => 'form'])
            ->add('calcTime', TextType::class, ['label' => 'billableTime', 'required' => true, 'translation_domain' => 'form'])
            ->add('invoice', CheckboxType::class, ['label' => 'billed', 'required' => false, 'translation_domain' => 'form'])
            ->add('inReport', CheckboxType::class, ['label' => 'showInReport', 'required' => false, 'translation_domain' => 'form'])
            ->add('onsite', CheckboxType::class, ['label' => 'onSite', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-block mt-3'), 'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
            'user' => array(),
        ]);
    }
}
