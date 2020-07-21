<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AuditTomAbteilung;
use App\Entity\Forms;
use App\Entity\Produkte;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FormsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', TextType::class, ['label' => 'Titel', 'required' => true, 'translation_domain' => 'form'])
            ->add('description', TextareaType::class, ['label' => 'Beschreibung', 'required' => false, 'translation_domain' => 'form'])
            ->add('version', TextType::class, ['label' => 'Version', 'required' => true, 'translation_domain' => 'form'])
            ->add('departments', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomAbteilung::class,
                'choices' => $options['departments'],
                'label' => 'Zugeordnete Abteilungen',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                'required' => true
            ])
            ->add('products', EntityType::class, [
                'choice_label' => 'name',
                'class' => Produkte::class,
                'choices' => $options['products'],
                'label' => 'Zugeordnete Produkte/Dienstleistungen',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                'required' => true
            ])
            ->add('uploadFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'delete_label' => 'Löschen',
                'label' => 'Formular hochladen',
                'translation_domain' => 'form',
                'download_label' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Angelegt' => 0,
                    'In Bearbeitung' => 1,
                    'Prüfung' => 2,
                    'Zur Freigabe vorgelegt' => 3,
                    'Veraltet' => 4,],
                'label' => 'Status',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block mt-3'), 'label' => 'Speichern', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Forms::class,
            'departments' => array(),
            'products' => array()
        ]);
    }
}
