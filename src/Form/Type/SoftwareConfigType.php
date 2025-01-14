<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\SoftwareConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class SoftwareConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, ['label' => 'Bezeichnung der Konfiguration', 'required' => true, 'translation_domain' => 'form'])
            ->add('config', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Konfiguration', 'required' => true, 'translation_domain' => 'form'])
            ->add('activ', CheckboxType::class, ['label' => 'Konfiguration Aktiv', 'translation_domain' => 'form'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn'), 'label' => 'Speichern', 'translation_domain' => 'form'])
            ->add('uploadFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Löschen',
                'label' => 'Bild hochladen',
                'translation_domain' => 'form',
                'download_label' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SoftwareConfig::class,
            'processes' => array()
        ]);
    }
}
