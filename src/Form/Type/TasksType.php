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
            ->add('title', TextType::class, ['label' => 'Titel', 'required' => true, 'translation_domain' => 'form'])
            ->add('task', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Beschreibung der Aufgabe', 'required' => true, 'translation_domain' => 'form'])
            ->add('endDate', DateType::class, ['label' => 'Enddatum', 'format' => 'dd.MM.yyyy', 'required' => false, 'translation_domain' => 'form'])
            ->add('prio', ChoiceType::class, [
                'choices' => [
                    'Ohne Priorität' => 0,
                    'Wenig Wichtig' => 1,
                    'Normal' => 2,
                    'Wichtig' => 3,
                    'Sehr wichtig' => 4,],
                'label' => 'Priorität',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('assignedUser', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'Aufgabe zuweisen',
                'translation_domain' => 'form',
                'multiple' => false,
                'expanded' => false,
                'required' => false
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block mt-3'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'user' => array()
        ]);
    }
}
