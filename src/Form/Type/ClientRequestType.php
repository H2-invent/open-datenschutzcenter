<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\ClientRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class, ['label' => 'Betreff', 'required' => true, 'translation_domain' => 'form'])
            ->add('item', ChoiceType::class, [
                'choices' => [
                    'Antrag auf Auskunft' => 0,
                    'Antrag auf Berichtigung' => 10,
                    'Antrag auf Datenübertragung' => 20,
                    'Antrag auf Löschung' => 30],
                'label' => 'Grund der Anfrage',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => true
            ])
            ->add('email', TextType::class, ['label' => 'Email Adresse', 'required' => true, 'translation_domain' => 'form'])
            ->add('name', TextType::class, ['label' => 'Name des Antragstellers', 'required' => true, 'translation_domain' => 'form'])
            ->add('password', PasswordType::class, ['label' => 'Zugangspasswort festlegen', 'required' => true, 'translation_domain' => 'form'])
            ->add('description', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Beschreibung der Anfrage', 'required' => true, 'translation_domain' => 'form'])
            ->add('pgp', TextareaType::class, ['label' => 'OpenPDP Public Key für die Verschlüsselung von Emails', 'required' => false, 'translation_domain' => 'form'])
            ->add('gdpr', CheckboxType::class, ['label' => 'Ich habe die Datenschutzhinweise gelesen und akzeptiere die Verarbeitung meiner Daten. Diese Verarbeitung ist notwendig, damit wir Ihre Anfrage beantworten können.', 'required' => true, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block mt-3'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClientRequest::class,
        ]);
    }
}
