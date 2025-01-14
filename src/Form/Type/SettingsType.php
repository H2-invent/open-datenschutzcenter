<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\Settings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('groupMapping', ChoiceType::class, [
                'label' => 'groupMapping',
                'help'=> 'groupMappingHelp',
                'translation_domain' => 'form',
                'expanded' => true,
                'choices' => [
                    'noGroupMapping' => Settings::NO_GROUP_MAPPING,
                    'useKeycloakGroups' => Settings::KEYCLOAK_GROUP_MAPPING,
                    'useApiGroups' => Settings::API_GROUP_MAPPING,
                ]
            ])
            ->add('save', SubmitType::class, [
                'attr' => array('class' => 'btn'),
                'label' => 'save',
                'translation_domain' => 'form'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Settings::class
        ]);
    }
}
