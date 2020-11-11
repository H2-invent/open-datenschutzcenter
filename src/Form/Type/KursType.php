<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AkademieKurse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class, ['label' => 'Titel', 'required' => true, 'translation_domain' => 'form'])
            ->add('video', TextType::class, ['label' => 'Videolink', 'required' => true, 'translation_domain' => 'form'])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Lokal/Cloud Storage oder CDN' => 0,
                    'Vimeo' => 1,],
                'label' => 'Videotyp angeben',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => true,
                'attr' => [
                    'class' => 'selectpicker',
                ],
            ])
            ->add('beschreibung', TextareaType::class, ['attr' => ['rows' => 12], 'label' => 'Beschreibung', 'required' => true, 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AkademieKurse::class,
        ]);
    }
}
