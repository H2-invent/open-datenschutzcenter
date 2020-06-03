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
use App\Entity\Team;
use App\Entity\Tom;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('titel', TextType::class, ['attr' => ['rows' => 8],'label' => 'Title, Anwendung, Verarbeitung', 'required' => true, 'translation_domain' => 'form'])
            ->add('beschreibung', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Beschreibung der technischen und organisatorischen Maßnahmen', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomPseudo', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Pseudonymisierung Verschlüsselung (Art. 32 Abs. 1 lit. a) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomZutrittskontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Zutrittskontrolle -> Vertraulichkeit (Art. 32 Abs. 1 lit. b) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomZugangskontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Zugangskontrolle -> Vertraulichkeit (Art. 32 Abs. 1 lit. b) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomZugriffskontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Zugriffskontrolle -> Vertraulichkeit (Art. 32 Abs. 1 lit. b) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomBenutzerkontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Benutzerkontrolle -> Vertraulichkeit (Art. 32 Abs. 1 lit. b) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomSpeicherkontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Speicherkontrolle -> Vertraulichkeit (Art. 32 Abs. 1 lit. b) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomTrennbarkeit', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Trennbarkeit -> Vertraulichkeit (Art. 32 Abs. 1 lit. b) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomDatenintegritaet', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Datenintegrität -> Integrität (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomTransportkontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Transportkontrolle -> Integrität (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomUebertragungskontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Übertragungskontrolle -> Integrität (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomEingabekontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Eingabekontrolle -> Integrität (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomZuverlaessigkeit', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Zuverlässigkeit -> Integrität (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomAuftragskontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Auftragskontrolle -> Integrität (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomVerfuegbarkeitskontrolle', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Verfügbarkeitskontrolle -> Verfügbarkeit und Belastbarkeit (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomWiederherstellbarkeit', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Wiederherstellbarkeit -> Verfügbarkeit und Belastbarkeit (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])
            ->add('tomAudit', TextareaType::class, ['attr' => ['rows' => 8],'label' => 'Verfahren zur regelmässigen Überprüfung, Bewertung und Evaluierung (Art. 32 Abs. 1 lit. c) DSGVO)', 'required' => true, 'translation_domain' => 'form'])

            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'Speichern', 'translation_domain' => 'form']);
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
