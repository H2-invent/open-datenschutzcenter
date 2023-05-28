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
                    'client' => 1,
                    'contractor' => 2,],
                'label'=>'contactFunction',
                'translation_domain' => 'form',
                'multiple' =>false,
            ])
            ->add('firma', TextType::class, ['label' => 'company', 'required' => true, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'number', 'required' => false, 'translation_domain' => 'form'])
            ->add('anrede', TextType::class, ['label' => 'salutation', 'required' => false, 'translation_domain' => 'form'])
            ->add('vorname', TextType::class, ['label' => 'firstName', 'required' => false, 'translation_domain' => 'form'])
            ->add('nachname', TextType::class, ['label' => 'lastName', 'required' => true, 'translation_domain' => 'form'])
            ->add('strase', TextType::class, ['label' => 'street', 'required' => true, 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'postcode', 'required' => true, 'translation_domain' => 'form'])
            ->add('ort', TextType::class, ['label' => 'city', 'required' => true, 'translation_domain' => 'form'])
            ->add('email', TextType::class, ['label' => 'email', 'required' => false, 'translation_domain' => 'form'])
            ->add('telefon', TextType::class, ['label' => 'phone', 'required' => false, 'translation_domain' => 'form'])
            ->add('bemerkung', TextareaType::class, ['label' => 'comment', 'required' => false, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Kontakte::class,
        ]);
    }
}
