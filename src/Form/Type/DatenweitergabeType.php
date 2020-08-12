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
            ->add('gegenstand', TextareaType::class, ['label' => 'Vertragsgegenstand', 'required' => true, 'translation_domain' => 'form'])
            ->add('notes', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Bemerkungen', 'required' => false, 'translation_domain' => 'form'])
            ->add('nummer', TextType::class, ['label' => 'Nummer der Datenweitergabe', 'required' => true, 'translation_domain' => 'form'])
            ->add('verantwortlich', TextType::class, ['label' => 'Verantwortlich für die Datenweitergabe (Intern)', 'required' => true, 'translation_domain' => 'form'])
            ->add('vertragsform', TextType::class, ['label' => 'Vertragsform', 'required' => true, 'translation_domain' => 'form'])
            ->add('reference', TextType::class, ['label' => 'Aktenzeichen', 'required' => false, 'translation_domain' => 'form'])
            ->add('zeichnungsdatum', DateType::class, ['label' => 'Zeichnungsdatum', 'required' => true, 'translation_domain' => 'form', 'widget' => 'single_text'])
            ->add('checkItems', CheckboxType::class, ['label' => 'Gegenstand und Dauer der Verarbeitung, Art und Zweck der Verarbeitung', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkPeople', CheckboxType::class, ['label' => 'Art der personenbezogenen Daten', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkData', CheckboxType::class, ['label' => 'Kategorien betroffener Personen', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkDocumentation', CheckboxType::class, ['label' => 'Verarbeitung auf dokumentierte Weisung des Verantwortlichen', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkTom', CheckboxType::class, ['label' => 'Ergreifen erforderlicher technischer und organisatorischer Maßnahmen', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkSubcontractor', CheckboxType::class, ['label' => 'Inanspruchnahme von Unterauftragnehmern nach Art. Art. 28 Abs. 2 und Abs. 4 DSGVO', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkTrust', CheckboxType::class, ['label' => 'Vertraulichkeitsverpflichtung', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkSupport', CheckboxType::class, ['label' => 'Unterstützung des Verantwortlichen bei der Beantwortung von Betroffenenanfragen', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkSupport3236', CheckboxType::class, ['label' => 'Unterstützung des Verantwortlichen bei den Pflichten nach Art. 32 – 36 DSGVO', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkNotes', CheckboxType::class, ['label' => 'Hinweispflicht', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkEnding', CheckboxType::class, ['label' => 'Umgang mit personenbezogenen Daten nach Beendigung der Auftragsverarbeitung', 'required' => false, 'translation_domain' => 'form'])
            ->add('checkAudit', CheckboxType::class, ['label' => 'Zurverfügungstellung von Informationen und Ermöglichung von Überprüfungen', 'required' => false, 'translation_domain' => 'form'])
            ->add('kontakt', EntityType::class, [
                'choice_label' => 'firma',
                'class' => Kontakte::class,
                'choices' => $options['kontakt'],
                'label' => 'Kontakt',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('verfahren', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVT::class,
                'choices' => $options['verfahren'],
                'label' => 'Zugehörige Verarbeitungstätigkeit',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'expanded' => true
            ])
            ->add('stand', EntityType::class, [
                'choice_label' => 'name',
                'class' => DatenweitergabeStand::class,
                'choices' => $options['stand'],
                'label' => 'Status',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('grundlage', EntityType::class, [
                'choice_label' => 'name',
                'class' => DatenweitergabeGrundlagen::class,
                'choices' => $options['grundlage'],
                'label' => 'Grundlage für die Verarbeitung',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('software', EntityType::class, [
                'choice_label' => 'name',
                'class' => Software::class,
                'choices' => $options['software'],
                'label' => 'Software, die in der Weitergabe involviert ist',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true
            ])
            ->add('uploadFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => false,
                'delete_label' => 'Löschen',
                'label' => 'Dokument zur Datenweitergabe hochladen',
                'translation_domain' => 'form',
                'download_label' => false
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block'), 'label' => 'Speichern', 'translation_domain' => 'form']);
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
