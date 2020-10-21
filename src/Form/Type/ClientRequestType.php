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
                    'Ich weiß nicht, welchen Grund ich angeben soll' => 0,
                    'Antrag auf Auskunft nach Art. 15 DSGVO' => 15,
                    'Antrag auf Berichtigung nach Art. 16 DSGVO' => 16,
                    'Antrag auf Einschränkung nach Art. 18 DSGVO' => 18,
                    'Antrag auf Datenübertragung/Herausgabe nach Art. 20 DSGVO' => 20,
                    'Antrag auf Löschung (Recht auf Vergessenwerden) nach Art. 17 DSGVO' => 17,
                    'Antrag auf Widerruf einer Einwilligung nach Art. 7 Abs. 3 DSGVO' => 73,
                    'Antrag auf einzelfallbezogenes Widerspruchsrecht nach Art. 21 DSGVO' => 21,
                    'Einreichnung einer Beschwerde nach Art. 77 DSGVO' => 77],
                'label' => 'Grund der Anfrage',
                'translation_domain' => 'form',
                'multiple' => false,
                'help' => 'Wenn Sie nicht wissen, welchen Grund nach der DSGVO Sie angeben müssen werden wir entsprechend Ihrer Beschreibung den Grund nachträglich eintragen',
                'required' => true
            ])
            ->add('email', TextType::class, ['label' => 'Email Adresse', 'required' => true, 'help' => 'Wir nutzen diese Email Adresse um Ihnen Updates zu Ihrer Anfrage zu schicken. Sie müssen die Email Adresse nach dem Absenden bestätigen.', 'translation_domain' => 'form'])
            ->add('name', TextType::class, ['label' => 'Name des Antragstellers', 'required' => true, 'translation_domain' => 'form'])
            ->add('password', PasswordType::class, ['label' => 'Zugangspasswort festlegen', 'required' => true, 'help' => 'Für jede Anfrage muss ein neues Passwort festgelegt werden. Bitte verwenden Sie ihr Passwort nur einmal um Ihre Daten bestmöglich zu schützen.', 'translation_domain' => 'form'])
            ->add('description', TextareaType::class, ['attr' => ['rows' => 10], 'label' => 'Beschreibung der Anfrage', 'required' => true, 'translation_domain' => 'form'])
            ->add('pgp', TextareaType::class, ['attr' => ['rows' => 12], 'label' => 'OpenPGP Public Key für die Verschlüsselung von Emails. (Alle Benachrichtigungen werden mit Ihrem öffentlichen Schlüssel verschlüsselt)', 'required' => false, 'translation_domain' => 'form'])
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
