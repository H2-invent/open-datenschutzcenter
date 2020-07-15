<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, ['label' => 'Teamname', 'required' => true, 'translation_domain' => 'form'])
            ->add('strasse', TextType::class, ['label' => 'Straße', 'required' => true, 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'PLZ', 'required' => true, 'translation_domain' => 'form'])
            ->add('stadt', TextType::class, ['label' => 'Stadt', 'required' => true, 'translation_domain' => 'form'])
            ->add('email', TextType::class, ['label' => 'E-Mail', 'required' => true, 'translation_domain' => 'form'])
            ->add('telefon', TextType::class, ['label' => 'Telefon', 'required' => true, 'translation_domain' => 'form'])
            ->add('dsb', TextType::class, ['label' => 'Datenschutzbeauftragter', 'required' => true, 'translation_domain' => 'form'])
            ->add('ceo', TextType::class, ['label' => 'Geschäftsführung', 'required' => true, 'translation_domain' => 'form'])
            ->add('signature', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'Email Signatur', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
