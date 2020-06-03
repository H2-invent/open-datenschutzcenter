<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AkademieKurse;
use App\Entity\AuditTomZiele;
use App\Entity\Kontakte;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KursAnmeldungType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('user', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label'=>'Benutzer',
                'translation_domain' => 'form',
                'multiple' =>true,
            ])
            ->add('zugewiesen', DateType::class, ['label' => 'Zugewiesen am', 'required' => true, 'translation_domain' => 'form'])
            ->add('wiedervorlage', ChoiceType::class, [
                'choices'  => [
                    'Keine Wiederholung' => null,
                    'In 30 Tagen' => '30 days',
                    'In 150 Tagen' => '150 days',
                    'In 300 Tagen' => '300 days',
                    'In 365 Tagen' => '365 days',
                ],
                'label'=>'Widerholung des Kurses',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' =>false,
            ])
            ->add('invite', CheckboxType::class, ['label' => 'Einladungs-EMail an alle Nutzer senden (Wenn nicht ausgewählt wird die Einladung automatisch am nächsten Tag verschickt)', 'required' => false, 'translation_domain' => 'form'])

            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'Kurse zuweisen', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => array(),
        ]);
    }
}
