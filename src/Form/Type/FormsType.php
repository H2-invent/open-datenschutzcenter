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
            ->add('title', TextType::class, ['label' => 'title', 'required' => true, 'translation_domain' => 'form'])
            ->add('description', TextareaType::class, ['label' => 'description', 'required' => false, 'translation_domain' => 'form'])
            ->add('version', TextType::class, ['label' => 'version', 'required' => true, 'translation_domain' => 'form'])
            ->add('departments', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomAbteilung::class,
                'choices' => $options['departments'],
                'label' => 'relatedDepartments',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('products', EntityType::class, [
                'choice_label' => 'name',
                'class' => Produkte::class,
                'choices' => $options['products'],
                'label' => 'relatedProducts',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('uploadFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'delete_label' => 'Löschen',
                'label' => 'uploadForm',
                'translation_domain' => 'form',
                'download_label' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'created' => 0,
                    'inProgress' => 1,
                    'inReview' => 2,
                    'submitted' => 3,
                    'outdated' => 4,],
                'label' => 'status',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block mt-3'), 'label' => 'save', 'translation_domain' => 'form']);
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
