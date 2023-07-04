<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Team;
use App\Repository\SettingsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    private $settings;

    public function __construct(SettingsRepository $settingsRepository)
    {
        $this->settings = $settingsRepository->findOne();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('name', TextType::class, ['label' => 'teamName', 'required' => true, 'translation_domain' => 'form']);

        if ($this->settings && $this->settings->getUseKeycloakGroups()) {
            $builder->add('keycloakGroup', TextType::class, ['label' => 'keycloakGroup', 'help' => 'keycloakGroupHelp', 'required' => false, 'translation_domain' => 'form']);
        }

        if ($options['teams']) {
            $builder
                ->add('parent', EntityType::class, [
                    'choice_label' => 'name',
                    'class' => Team::class,
                    'choices' => $options['teams'],
                    'label' => 'parentTeam.word',
                    'required' => false,
                    'translation_domain' => 'form',
                    'multiple' => false,
                    'help' => 'parentTeam.help',
                    'attr' => [
                        'class' => 'selectpicker',
                        'data-live-search' => 'true'
                    ],
                ]);
        }

        $builder
            ->add('strasse', TextType::class, ['label' => 'street', 'required' => true, 'translation_domain' => 'form'])
            ->add('plz', TextType::class, ['label' => 'postcode', 'required' => true, 'translation_domain' => 'form'])
            ->add('stadt', TextType::class, ['label' => 'city', 'required' => true, 'translation_domain' => 'form'])
            ->add('email', TextType::class, ['label' => 'email', 'required' => true, 'translation_domain' => 'form'])
            ->add('telefon', TextType::class, ['label' => 'phone', 'required' => true, 'translation_domain' => 'form'])
            ->add('dsb', TextType::class, ['label' => 'dsb', 'required' => false, 'translation_domain' => 'form'])
            ->add('ceo', TextType::class, ['label' => 'ceo', 'required' => true, 'translation_domain' => 'form'])
            ->add('industry', TextType::class, ['label' => 'industry', 'required' => false, 'translation_domain' => 'form'])
            ->add('specialty', TextType::class, ['label' => 'specialty', 'required' => false, 'translation_domain' => 'form'])
            ->add('signature', TextareaType::class, ['attr' => ['rows' => 8], 'label' => 'signature', 'required' => false, 'translation_domain' => 'form'])
            ->add('slug', TextType::class, ['label' => 'slug', 'required' => false, 'translation_domain' => 'form', 'help' => 'slugHelp'])
            ->add('externalLink', TextType::class, ['label' => 'externalLink', 'required' => false, 'translation_domain' => 'form', 'help' => 'externalLinkHelp'])
            ->add('video', TextType::class, ['label' => 'jitsiLink', 'required' => false, 'translation_domain' => 'form', 'help' => 'jitsiLinkHelp'])
            ->add('save', SubmitType::class, ['attr' => array('class' => 'btn btn-primary'),'label' => 'save', 'translation_domain' => 'form']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
            'teams' => array(),
        ]);
    }
}
