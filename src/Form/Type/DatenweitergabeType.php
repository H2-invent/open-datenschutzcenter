<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Datenweitergabe;
use App\Entity\DatenweitergabeGrundlagen;
use App\Entity\DatenweitergabeStand;
use App\Entity\Kontakte;
use App\Entity\Software;
use App\Entity\VVT;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class DatenweitergabeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('gegenstand', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'contractObject', 'required' => true, 'translation_domain' => 'form', 'help' => 'contractObjectHelp'])
            ->add('notes', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'comments', 'required' => false, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'dataTransferNumber', 'required' => true, 'translation_domain' => 'form'])
            ->add('verantwortlich', TextType::class, ['label' => 'responsibleParty', 'required' => true, 'translation_domain' => 'form'])
            ->add('vertragsform', TextType::class, ['label' => 'contractType', 'required' => true, 'translation_domain' => 'form'])
            ->add('reference', TextType::class, ['label' => 'reference', 'required' => false, 'translation_domain' => 'form', 'help' => 'referenceHelp'])
            ->add('zeichnungsdatum', DateType::class, ['label' => 'signDate', 'required' => true, 'translation_domain' => 'form', 'widget' => 'single_text', 'help' => 'signDateHelp'])
            ->add('checkItems', CheckboxType::class, ['label' => 'dataTransferParameters', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkPeople', CheckboxType::class, ['label' => 'personalDataType', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkData', CheckboxType::class, ['label' => 'affectedPersons', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkDocumentation', CheckboxType::class, ['label' => 'documentedInstructions', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkTom', CheckboxType::class, ['label' => 'technicalAndOrganisationalMeasures', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkSubcontractor', CheckboxType::class, ['label' => 'subcontractors', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkTrust', CheckboxType::class, ['label' => 'privacyObligation', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkSupport', CheckboxType::class, ['label' => 'supportWithInquiries', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkSupport3236', CheckboxType::class, ['label' => 'supportWithObligations', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkNotes', CheckboxType::class, ['label' => 'dutyToInform', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkEnding', CheckboxType::class, ['label' => 'handlingOfDataAfterProcessing', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkAudit', CheckboxType::class, ['label' => 'resources', 'required' => false, 'translation_domain' => 'form'])
            ->add('kontakt', EntityType::class, [
                'choice_label' => 'firma',
                'class' => Kontakte::class,
                'choices' => $options['kontakt'],
                'label' => 'contact',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ]
            ])
            ->add('verfahren', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVT::class,
                'choices' => $options['verfahren'],
                'label' => 'relatedProcessingActivity',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'relatedProcessingActivityHelp'
            ])
            ->add('stand', EntityType::class, [
                'choice_label' => 'name',
                'class' => DatenweitergabeStand::class,
                'choices' => $options['stand'],
                'label' => 'status',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                ],
            ])
            ->add('grundlage', EntityType::class, [
                'choice_label' => 'name',
                'class' => DatenweitergabeGrundlagen::class,
                'choices' => $options['grundlage'],
                'label' => 'dataTransferBasis',
                'translation_domain' => 'form',
                'multiple' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
                'help' => 'dataTransferBasisHelp'
            ])
            ->add('software', EntityType::class, [
                'choice_label' => 'name',
                'class' => Software::class,
                'choices' => $options['software'],
                'label' => 'relatedSoftware',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'selectpicker',
                    'data-live-search' => 'true'
                ],
            ])
            ->add('uploadFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'delete_label' => 'delete',
                'label' => 'dataTransferDocument',
                'translation_domain' => 'form',
                'download_label' => false
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block'), 'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Datenweitergabe::class,
            'stand' => array(),
            'grundlage' => array(),
            'kontakt' => array(),
            'verfahren' => array(),
            'software' => array()
        ]);
    }
}
