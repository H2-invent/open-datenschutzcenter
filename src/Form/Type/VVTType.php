<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

 /**
 * Modified by
 * User: Jan Juister
 * Date: 13.05.2022
 */

namespace App\Form\Type;

use App\Entity\AuditTomAbteilung;
use App\Entity\Datenweitergabe;
use App\Entity\Produkte;
use App\Entity\Software;
use App\Entity\Tom;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VVTType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('nummer', TextType::class, ['label' => 'processingActivityNumber', 'required' => true, 'translation_domain' => 'form'])
            ->add('name', TextType::class, ['label' => 'processingActivityDenomination', 'required' => true, 'translation_domain' => 'form'])
            ->add('verantwortlich', TextareaType::class, ['label' => 'furtherResponsibleParties', 'required' => false, 'translation_domain' => 'form'])
            ->add('userContract', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'internalResponsibleParty',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'internalResponsiblePartyHelp'
            ])
            ->add('software', EntityType::class, [
                'choice_label' => 'name',
                'class' => Software::class,
                'choices' => $options['software'],
                'label' => 'processingActivityUsedSoftware',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'processingActivityUsedSOftwareHelp'
            ])
            ->add('zweck', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'processingActivityPurpose', 'required' => true, 'translation_domain' => 'form', 'help' => 'processingActivityPurposeHelp'])
            ->add('jointControl', CheckboxType::class, ['label' => 'jointControl', 'required' => false, 'translation_domain' => 'form', 'help' => 'jointControlHelp'])
            ->add('auftragsverarbeitung', CheckboxType::class, ['label' => 'isContract', 'required' => false, 'translation_domain' => 'form', 'help' => 'isContractHelp'])
            ->add('speicherung', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'processingActivityDataStorage', 'required' => true, 'translation_domain' => 'form', 'help' => 'processingActivityDataStorage'])
            ->add('loeschfrist', TextareaType::class, ['attr' => ['readonly'=>true, 'class' => 'summernote summernote-disable'], 'label' => 'deleteDeadline', 'required' => false, 'translation_domain' => 'form', 'help' => 'deleteDeadlineHelp'])
            ->add('weitergabe', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'processingActivityDataTransferPartners', 'required' => false, 'translation_domain' => 'form', 'help' => 'processingActivityDataTransferPartners'])
            ->add('grundlage', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTGrundlage::class,
                'choices' => $options['grundlage'],
                'label' => 'processingActivityBasis',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('personengruppen', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTPersonen::class,
                'choices' => $options['personen'],
                'label' => 'processingActivityPeople',
                'translation_domain' => 'form',
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('datenweitergaben', EntityType::class, [
                'choice_label' => 'gegenstand',
                'class' => Datenweitergabe::class,
                'choices' => $options['daten'],
                'label' => 'processingActivityDataTransfers',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('kategorien', EntityType::class, [
                'class' => VVTDatenkategorie::class,
                'choices' => $options['kategorien'],
                'label' => 'processingActivityCategories',
                'translation_domain' => 'form',
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'processingActivityCategoriesHelp'
            ])
            ->add('eu', CheckboxType::class, ['label' => 'processingActivityDataTransferOutsideEU', 'required' => false, 'translation_domain' => 'form', 'help' => 'processingActivityDataTransferOutsideEuHelp'])
            ->add('tomLink', EntityType::class, [
                'choice_label' => 'titel',
                'class' => Tom::class,
                'choices' => $options['tom'],
                'label' => 'processingActivityTOM',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'processingActivityTOM'
            ])
            ->add('tom', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'processingActivityFurtherMeasures', 'required' => false, 'translation_domain' => 'form', 'help' => 'processingActivityFurtherMeasuresHelp'])
            ->add('risiko', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTRisiken::class,
                'choices' => $options['risiken'],
                'label' => 'processingActivityRiskSources',
                'translation_domain' => 'form',
                'multiple' => true,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'processingActivityRiskSourcesHelp'
            ])
            ->add('status', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTStatus::class,
                'choices' => $options['status'],
                'label' => 'status',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                ],
                'help' => 'processingActivityStatusHelp'
            ])
            ->add('abteilung', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomAbteilung::class,
                'choices' => $options['abteilung'],
                'label' => 'processingActivityDepartment',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'processingActivityDepartment'
            ])
            ->add('source', TextareaType::class, ['attr' => ['row' => 8], 'label' => 'processingActivityDataCollection', 'required' => false, 'translation_domain' => 'form', 'help' => 'processingActivityDataCollectionHelp'])
            ->add('informationspflicht', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'informationObligation', 'required' => false, 'translation_domain' => 'form', 'help' => 'informationObligationHelp'])
            ->add('dsb', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'dsbComment', 'required' => false, 'translation_domain' => 'form', 'help' => 'dsbCommentHelp'])
            ->add('beurteilungEintritt', ChoiceType::class, [
                'choices' => [
                    'nothingSelected' => 0,
                    'lowRisk' => 1,
                    'someRisk' => 2,
                    'significantRisk' => 3,
                    'highRisk' => 4,
                ],
                'label' => 'processingActivityRiskProbability',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                ],
                'help' => 'processingActivityRiskProbabilityHelp'
            ])
            ->add('beurteilungSchaden', ChoiceType::class, [
                'choices' => [
                    'nothingSelected' => 0,
                    'littleDamage' => 1,
                    'someDamage' => 2,
                    'significantDamage' => 3,
                    'criticalDamage' => 4,
                ],
                'label' => 'processingActivityRiskDamage',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                ],
                'help' => 'processingActivityRiskDamageHelp'
            ])
            ->add('produkt', EntityType::class, [
                'choice_label' => 'name',
                'class' => Produkte::class,
                'choices' => $options['produkte'],
                'label' => 'processingActivityProducts',
                'help' => 'processingActivityProductsHelp',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block'), 'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VVT::class,
            'grundlage' => array(),
            'personen' => array(),
            'kategorien' => array(),
            'risiken' => array(),
            'status' => array(),
            'user' => array(),
            'daten' => array(),
            'tom' => array(),
            'abteilung' => array(),
            'produkte' => array(),
            'software' => array()
        ]);
    }
}
