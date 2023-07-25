<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Tom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('titel', TextType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomTitle',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('beschreibung', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'descriptionOfMeasures',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomPseudo', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomEncryption',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomZutrittskontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomPhysicalAccess',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomZugangskontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomAuthenticatedAccess',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomZugriffskontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomPrivilegedAccess',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomBenutzerkontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomUserControl',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomSpeicherkontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomStorageControl',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomTrennbarkeit', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomSeparability',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomDatenintegritaet', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomDataIntegrity',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomTransportkontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomTransportControl',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomUebertragungskontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomTransferControl',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomEingabekontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomInputControl',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomZuverlaessigkeit', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomReliability',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomAuftragskontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomAssignmentControl',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomVerfuegbarkeitskontrolle', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomAvailabilityControl',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomWiederherstellbarkeit', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomRecoverability',
                'required' => true,
                'translation_domain' => 'form'
            ])
            ->add('tomAudit', TextareaType::class, [
                'attr' => ['rows' => 8],
                'label' => 'tomAuditProcedure',
                'required' => true,
                'translation_domain' => 'form'
            ])

            ->add('save', SubmitType::class, [
                'attr' => array('class' => 'btn btn-primary'),
                'label' => 'save',
                'translation_domain' => 'form'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tom::class,
            'ziele' => array(),
            'abteilungen' => array(),
            'status' => array(),
        ]);
    }
}
