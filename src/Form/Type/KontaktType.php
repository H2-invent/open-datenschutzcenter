<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AuditTomZiele;
use App\Entity\Kontakte;
use App\Entity\Team;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KontaktType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('art', ChoiceType::class, [
                'choices'  => [
                    'Auftraggeber' => 1,
                    'Auftragnehmer' => 2,],
                'label'=>'Funktion des Kontakts',
                'translation_domain' => 'form',
                'multiple' =>false,
            ])
            ->add('firma', TextType::class, ['label' => 'Firma', 'required' => true, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'Nummer', 'required' => false, 'translation_domain' => 'form'])
            ->add('anrede', TextType::class, ['label' => 'Anrede', 'required' => false, 'translation_domain' => 'form'])
            ->add('vorname', TextType::class, ['label' => 'Vorname', 'required' => false, 'translation_domain' => 'form'])
            ->add('nachname', TextType::class, ['label' => 'Nachname', 'required' => true, 'translation_domain' => 'form'])
            ->add('strase', TextType::class, ['label' => 'Straße', 'required' => true, 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'PLZ', 'required' => true, 'translation_domain' => 'form'])
            ->add('ort', TextType::class, ['label' => 'Ort', 'required' => true, 'translation_domain' => 'form'])
            ->add('email', TextType::class, ['label' => 'E-Mail', 'required' => false, 'translation_domain' => 'form'])
            ->add('telefon', TextType::class, ['label' => 'Telefon', 'required' => false, 'translation_domain' => 'form'])
            ->add('bemerkung', TextareaType::class, ['label' => 'Bemerkung', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Kontakte::class,
        ]);
    }
}
