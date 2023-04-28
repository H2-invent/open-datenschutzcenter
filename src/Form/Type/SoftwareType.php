<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Datenweitergabe;
use App\Entity\Software;
use App\Entity\VVT;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoftwareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, ['label' => 'softwareName', 'required' => true, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'softwareNumber', 'required' => false, 'translation_domain' => 'form'])
            ->add('description', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'softwareDescription', 'required' => true, 'translation_domain' => 'form'])
            ->add('licenseType', ChoiceType::class, [
                'choices' => [
                    'notSpecified' => 0,
                    'openSource' => 10,
                    'closedSource' => 20,
                    'userLicence' => 30,
                    'deviceLicence' => 40,
                    'serverLicence' => 50,
                    'managedService' => 60,
                    'mixedLicence' => 70,
                    'otherLicence' => 90,],
                'label' => 'licenceType',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('license', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'licenceDescription', 'required' => false, 'translation_domain' => 'form'])
            ->add('licenseExpiration', DateType::class, ['label' => 'expirationDate', 'required' => false, 'translation_domain' => 'form', 'widget' => 'single_text'])
            ->add('reference', TextType::class, ['label' => 'softwareReference', 'required' => false, 'translation_domain' => 'form'])
            ->add('vvts', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVT::class,
                'choices' => $options['processes'],
                'label' => 'relatedProcesses',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('datenweitergabe', EntityType::class, [
                'choice_label' => 'gegenstand',
                'class' => Datenweitergabe::class,
                'choices' => $options['datenweitergabe'],
                'label' => 'relatedDataTransfers',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('purchase', DateType::class, ['label' => 'purchaseDate', 'required' => false, 'translation_domain' => 'form', 'widget' => 'single_text'])
            ->add('build', TextType::class, ['label' => 'version', 'required' => true, 'translation_domain' => 'form'])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'created' => 0,
                    'inProgress' => 10,
                    'inReview' => 20,
                    'submitted' => 30,
                    'inactive' => 60,],
                'label' => 'status',
                'translation_domain' => 'form',
                'multiple' => false,
                'help' => 'softwareStatusHelp'
            ])
            ->add('location', TextType::class, ['label' => 'softwareLocation', 'required' => false, 'translation_domain' => 'form'])
            ->add('archiving', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'backupPolicy', 'required' => false, 'translation_domain' => 'form', 'help' => 'backupPolicyHelp'])
            ->add('recovery', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'emergencyPlan', 'required' => false, 'translation_domain' => 'form', 'help' => 'emergencyPlanHelp'])
            ->add('permissions', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'privilegePolicy', 'required' => false, 'translation_domain' => 'form', 'help' => 'privilegePolicyHelp'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'), 'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Software::class,
            'processes' => array(),
            'datenweitergabe' => array(),
        ]);
    }
}
