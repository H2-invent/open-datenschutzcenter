<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AuditTom;
use App\Entity\AuditTomAbteilung;
use App\Entity\AuditTomStatus;
use App\Entity\AuditTomZiele;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuditTomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('frage', TextType::class, ['label' => 'question', 'required' => true, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'auditTomNumber', 'required' => true, 'translation_domain' => 'form'])
            ->add('bemerkung', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'comment', 'required' => true, 'translation_domain' => 'form'])
            ->add('empfehlung', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'recommendation', 'required' => true, 'translation_domain' => 'form'])
            ->add('ziele', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomZiele::class,
                'choices' => $options['ziele'],
                'label' => 'auditGoals',
                'translation_domain' => 'form',
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('abteilung', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomAbteilung::class,
                'choices' => $options['abteilungen'],
                'label' => 'departments',
                'translation_domain' => 'form',
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('status', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomStatus::class,
                'choices' => $options['status'],
                'label' => 'status',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('tomAttribut', TextType::class, ['label' => 'globalTomAttribute', 'required' => false, 'translation_domain' => 'form'])
            ->add('tomZiel', ChoiceType::class, [
                'choices'  => [
                    'nothingSelectedTomView' => null,
                    'encryption' => 1,
                    'physicalAccessControl' => 2,
                    'authenticatedAccessControl' => 3,
                    'privilegedAccessControl' => 4,
                    'userControl' => 5,
                    'storageControl' => 6,
                    'separability' => 7,
                    'dataIntegrity' => 8,
                    'transportControl' => 9,
                    'transferControl' => 10,
                    'inputControl' => 11,
                    'reliability' => 12,
                    'assignmentControl' => 13,
                    'availabilityControl' => 14,
                    'recoverability' => 15,
                    'evaluation' => 16,
                ],
                'label' => 'globalTomPosition',
                'translation_domain' => 'form',
                'required' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('kategorie', ChoiceType::class, [
                'choices'  => [
                    'nothingSelected' => '',
                    'privacyManagement' => 'privacyManagement',
                    'privacyTemplates' => 'privacyTemplates',
                    'instructions' => 'instructions',
                    'emergencyPlanAndDocumentation' => 'emergencyPlanAndDocumentation',
                    'structuralSafety' => 'structuralSafety',
                    'video' => 'video',
                    'authentications' => 'authentications',
                    'privileges' => 'privileges',
                    'logs' => 'logs',
                    'backups' => 'backups',
                    'deletion' => 'deletion',
                    'transfer' => 'transfer',
                    'remoteMaintenance' => 'remoteMaintenance',
                    'mobileDevices' => 'mobileDevices',
                    'wlan' => 'wlan',
                    'marketing' => 'marketing',
                    'cloud' => 'cloud',
                    'archive' => 'archive',
                    'ITSec' => 'ITSec',
                    'MFC' => 'MFC',
                    'serverRoom' => 'serverRoom',
                    'clientCapability' => 'clientCapability',
                    'compliance' => 'compliance',
                    'GoBD' => 'GoBD',
                    'other' => 'other',
                ],
                'label' => 'category',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])

            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AuditTom::class,
            'ziele' => array(),
            'abteilungen' => array(),
            'status' => array(),
        ]);
    }
}
