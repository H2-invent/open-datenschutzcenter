<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TasksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class, ['label' => 'title', 'required' => true, 'translation_domain' => 'form'])
            ->add('task', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'taskDescription', 'required' => true, 'translation_domain' => 'form'])
            ->add('endDate', DateType::class, ['label' => 'endDate', 'widget' => 'single_text', 'required' => false, 'translation_domain' => 'form'])
            ->add('prio', ChoiceType::class, [
                'choices' => [
                    'noPriority' => 0,
                    'lowPriority' => 1,
                    'normalPriority' => 2,
                    'highPriority' => 3,
                    'veryHighPriority' => 4,],
                'label' => 'priority',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                ],
            ])
            ->add('assignedUser', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'assignTask',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block mt-3'), 'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'user' => array()
        ]);
    }
}
