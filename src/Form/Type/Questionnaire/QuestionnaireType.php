<?php
declare(strict_types=1);

namespace App\Form\Type\Questionnaire;

use App\Entity\Questionnaire;
use App\Form\Type\Template\BaseType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionnaireType extends BaseType
{
    private static string $TRANSLATION_DOMAIN = 'questionnaire';
    private static string $BASE_LABEL = 'questionnaire.';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'label',
            TextType::class,
            $this->getOptions(self::$BASE_LABEL.'label'),
        );

        $builder->add(
            'description',
            TextareaType::class,
            $this->getOptions(
                self::$BASE_LABEL.'description',
                false,
            ),
        );

        $builder->add(
            'percentageToPass',
            PercentType::class,
            $this->getOptions(self::$BASE_LABEL.'percentageToPass')
        );

        $builder->add(
            'save',
            SubmitType::class,
            [
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
                'label' => 'save.word',
                'translation_domain' => 'general',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Questionnaire::class,
            ],
        );
    }

    protected function getDefaultDomain(): string
    {
        return self::$TRANSLATION_DOMAIN;
    }
}