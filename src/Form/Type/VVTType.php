<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
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
            ->add('nummer', TextType::class, ['label' => 'Nummer der Verarbeitung', 'required' => true, 'translation_domain' => 'form'])
            ->add('name', TextType::class, ['label' => 'Bezeichung der Verarbeitung', 'required' => true, 'translation_domain' => 'form'])
            ->add('verantwortlich', TextareaType::class, ['label' => 'Verantwortliche Person (weitere)', 'required' => false, 'translation_domain' => 'form'])
            ->add('userContract', EntityType::class, [
                'choice_label' => 'email',
                'class' => User::class,
                'choices' => $options['user'],
                'label' => 'Verantwortliche Person intern',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => true,
                'help' => 'Wählen Sie hier den zuständigen Benutzer für diese Verarbeitung aus dem Datenschutzcenter. zu jeder Verarbeitung muss mindestens eine verantwortliche Person eingetragen werden.'
            ])
            ->add('software', EntityType::class, [
                'choice_label' => 'name',
                'class' => Software::class,
                'choices' => $options['software'],
                'label' => 'Verwendete Software in dieser Verarbeitung',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'help' => 'Bei Bedarf können Sie hier die Software auswählen, die in dieser Verarbeitung eingesetzt werden. Diese Angabe ist für die Analyse der Informationssicherheit und zur Umsetzung der technischen Maßnahmen wichtig. Mit getrückter "STRG" Taste können mehrere Optionen ausgewählt werden.'
            ])
            ->add('zweck', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Zweck der Verarbeitung', 'required' => true, 'translation_domain' => 'form', 'help' => 'Geben Sie hier den Zweck der Verarbeitung und eine Beschreibung der Verarbeitung an. Wenn möglich beschreiben Sie hier zusätzlich den Nutzen der Verarbeitung mit Fokus auf Ihre Unternehmenstätigkeit.'])
            ->add('jointControl', CheckboxType::class, ['label' => 'Ja, es handelt sich um eine Joint Control Verarbeitung (gemeinsame Verarbeitung)', 'required' => false, 'translation_domain' => 'form', 'help' => 'Gemeinsame Verarbeitung bedeutet, dass Ihr Unternehmen zusammen mit einer anderen Organisation die Dienstleistung anbietet und dafür Daten erfasst und verarbeitet, z.B. Facebook ist ein typisches Beispiel für eine gemeinsame Verarbeitung. In dieser Verabreitung werden Daten von Ihrer Organisation generiert, an Facebook weitergegeben und auch selber intern verarbeitet.'])
            ->add('auftragsverarbeitung', CheckboxType::class, ['label' => 'Ja, es handelt es sich um eine Verarbeitung im Auftrag einer weiteren Organisation', 'required' => false, 'translation_domain' => 'form', 'help' => 'Auftragsverarbeitung bedeutet, dass Ihre Organisation für eine weitere Organisation die Daten verarbeitet, meist in Form einer Dienstleistung. Ihre Organisation nutzt die Daten nur im Namen des Auftraggebers und nicht für eigene Zwecke.'])
            ->add('speicherung', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Wo werden die Daten gespeichert/abgelegt', 'required' => true, 'translation_domain' => 'form', 'help' => 'Geben Sie hier an wo und wie die Daten gespeichert werden. Die Speicherung ist eine wichtige Teilverarbeitung und sollte daher zusätzliche dokumentiert werden. Es ist auch im nachhinein einfacher den Datenfluss nachzuverfolgen, wenn die Speicherorte genau dokumentiert wurden.'])
            ->add('loeschfrist', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Löschfristen', 'required' => true, 'translation_domain' => 'form', 'help' => 'Die Löschfrist ist in der DSGVO nicht vorgegeben. Die Verantwortlichen müssen für jede Verarbeitung selber eine Löschfrist definieren, dokumentieren und argumentieren. Die Argumentation kann auf bestehenden gesetzlichen Archivierungsfristen beruhen, z.B. dem Handelsgesetz oder Steuergesetz. Die gesetzlichen Archivierungspflichten stellen jedoch nur die minimale Speicherdauer dar und keine Löschfrist.'])
            ->add('weitergabe', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'An folgende Unternehmen/Stellen/Funktionen werden die Daten weitergegeben', 'required' => false, 'translation_domain' => 'form', 'help' => 'Beschrieben Sie hier in Form einer Matrix/ Tabelle an welche Unternehmen oder Stellen Sie die Daten in Rahmen dieser Verarbeitung weitergeben. Nutzen Sie dafür die Angaben aus den vorherigen Felder "Personengruppen und Datenkategorien".'])
            ->add('grundlage', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTGrundlage::class,
                'choices' => $options['grundlage'],
                'label' => 'Beschreiben Sie, weshalb die Datenverarbeitung erforderlich ist? (Zweck und Grundlage) *',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                'required' => true
            ])
            ->add('personengruppen', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTPersonen::class,
                'choices' => $options['personen'],
                'label' => 'Die Daten welcher Personen werden verarbeitet? *',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('datenweitergaben', EntityType::class, [
                'choice_label' => 'gegenstand',
                'class' => Datenweitergabe::class,
                'choices' => $options['daten'],
                'label' => 'Welche Datenweitergaben werden dieser Verarbeitung zuordnen?',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false,
                'expanded' => true,
            ])
            ->add('kategorien', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTDatenkategorie::class,
                'choices' => $options['kategorien'],
                'label' => 'Welche Daten(kategorien) werden verarbeitet? *',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('eu', CheckboxType::class, ['label' => 'Ja, Daten werden außerhalb der EU verarbeitet', 'required' => false, 'translation_domain' => 'form', 'help' => 'Wenn Daten außerhalb der EU verarbeitet (gespeichert, erfasst, gelöscht) werden, müssen zusätzliche Vorsichtsmaßnahmen getroffen werden um die Daten nach der DSGVo zu schützen. Setzen Sie diesen Hacken, wenn Sie oder ein Auftragsverarbeiter die Daten die Daten im EU Außland verarbeitet.'])
            ->add('tomLink', EntityType::class, [
                'choice_label' => 'titel',
                'class' => Tom::class,
                'choices' => $options['tom'],
                'label' => 'Welche TOM wird für diese Verarbeitung verwendet?',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false,
                'help' => 'Wenn Sie bereits ein TOM Dokument im Datenschutzcenter angelegt haben, können Sie diese bei Bedarf der Verarbeitung zuweisen. Die Verknüpfung wird soäter auch im Dashboard und Datenflussplan angezeigt.'
            ])
            ->add('tom', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Weitere Hinweise zur TOM', 'required' => false, 'translation_domain' => 'form', 'help' => 'Geben Sie hier bei Bedarf weitere technische und organisatorische Maßnahmen an, die nur diese Verarbeitung betreffen.'])
            ->add('risiko', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTRisiken::class,
                'choices' => $options['risiken'],
                'label' => 'Mögliche Risko-Quellen',
                'translation_domain' => 'form',
                'multiple' => true,
                'expanded' => true,
                'help' => 'Geben Sie hier alle möglichen Risiken an die in dieser Verarbeitung auftreten können. Die Risiken können direkt, indirekt, technisch oder organisatorisch mit der Verarbeitung zusammenhängen.'
            ])
            ->add('status', EntityType::class, [
                'choice_label' => 'name',
                'class' => VVTStatus::class,
                'choices' => $options['status'],
                'label' => 'Status',
                'translation_domain' => 'form',
                'multiple' => false,
            ])
            ->add('abteilung', EntityType::class, [
                'choice_label' => 'name',
                'class' => AuditTomAbteilung::class,
                'choices' => $options['abteilung'],
                'label' => 'Zugeordnete Abteilung',
                'translation_domain' => 'form',
                'multiple' => false,
                'required' => false,
                'help' => 'Hier können Sie eine Abteilung zu der Verarbeitung hinzufügen. Die Abteilung ist hilfreich um den Datenflussplan filtern zu können und gleiche Verarbeitungen in unterschiedlichen Abteilungen eindeutig zu unterteilen.'
            ])
            ->add('informationspflicht', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Informationspflicht', 'required' => false, 'translation_domain' => 'form', 'help' => 'Es sollte je Verfahren dokumentiert werden, wo und wie den Informationspflichten jeweils nachgekommen wird. Dies kann bspw. durch Verweis auf Datenschutzhinweise, Vertragsbestandteile, Disclaimer in Formularen, bei Mitarbeiterdaten z.B. auch durch Verweise bei Erhebung auf intern bekanntgemachte Betriebsvereinbarung etc. erfolgen.'])
            ->add('dsb', TextareaType::class, ['attr' => ['class' => 'summernote'], 'label' => 'Kommentar des Datenschutzbeauftragten', 'required' => false, 'translation_domain' => 'form', 'help' => 'Neben der verpflichtenden Erfassung des Verarbeitungszwecks sollte zur Erfüllung der Rechenschaftpflichten gem. Art. 5 auch die Rechtsgrundlage mitsamt ggfs. erforderlicher Abwägungen, Einwilligungsklauseln und Prüfvermerken, ob bspw. die Anforderungen an Widerspruchsmöglichkeiten oder die automatisierte Einzelentscheidung berücksichtigt wurden.'])
            ->add('beurteilungEintritt', ChoiceType::class, [
                'choices' => [
                    'Bitte auswählen' => 0,
                    'Vernachlässigbar' => 1,
                    'Eingeschränk möglich' => 2,
                    'Signifikant' => 3,
                    'Sehr wahrscheinlich' => 4,
                ],
                'label' => 'Risiko: Eintrittswahrscheinlichkeit',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' => false,
                'help' => 'Für jede Verarbeitung muss auf Grundlage der ISO 27001 die Eintrittswahrscheinlichkeit dokumentiert werden. Dabei muss die Eintrittswahrscheinlichkeit festgelegt werden. Die Wahrscheinlichkeit lässt sich in vier Level unterteilen.'
            ])
            ->add('beurteilungSchaden', ChoiceType::class, [
                'choices' => [
                    'Bitte auswählen' => 0,
                    'Gering (kaum Auswirkung)' => 1,
                    'Eingeschränkt vorhanden' => 2,
                    'Signifikant' => 3,
                    'Hoch (schwerwiegend bis existenzbedrohend)' => 4,
                ],
                'label' => 'Risiko: Schadenspotenzial',
                'translation_domain' => 'form',
                'required' => true,
                'multiple' => false,
                'help' => 'Für jede Verarbeitung muss auf Grundlage der ISO 27001 das Schadenspotenzial dokumentiert werden.'
            ])
            ->add('produkt', EntityType::class, [
                'choice_label' => 'name',
                'class' => Produkte::class,
                'choices' => $options['produkte'],
                'label' => 'Zugeordnete Produkte',
                'help' => 'Mit gedrückter "STRG" Taste können mehrere Produkte ausgewählt werden',
                'translation_domain' => 'form',
                'multiple' => true,
                'required' => false
            ])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary btn-block'), 'label' => 'Speichern', 'translation_domain' => 'form']);
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
