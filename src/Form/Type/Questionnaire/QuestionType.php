<?php
declare(strict_types=1);

namespace App\Form\Type\Questionnaire;

use App\DataTypes\QuestionTypes;
use App\Entity\Question;
use App\Form\Type\Template\BaseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuestionType extends BaseType
{
    private static string $TRANSLATION_DOMAIN = 'questionnaire';
    private static string $BASE_LABEL = 'question.';

    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'label',
            TextType::class,
            $this->getOptions(self::$BASE_LABEL . 'label'),
        );

        $builder->add(
            'evalValue',
            PercentType::class,
            $this->getOptions(self::$BASE_LABEL . 'evaluationValue'),
        );

        $builder->add(
            'hint',
            TextType::class,
            $this->getOptions(self::$BASE_LABEL . 'hint', false),
        );

        $builder->add(
            'type',
            ChoiceType::class,
            $this->getOptions(
                self::$BASE_LABEL . 'type.word',
                additionalOptions: [
                    'choices' => [
                        $this->translator->trans(
                            self::$BASE_LABEL . 'type.checkbox',
                            domain: self::$TRANSLATION_DOMAIN
                        ) => QuestionTypes::CHECKBOX,
                        $this->translator->trans(
                            self::$BASE_LABEL . 'type.radio',
                            domain: self::$TRANSLATION_DOMAIN
                        ) => QuestionTypes::RADIO,
                    ]
                ]
            )
        );

        $builder->add(
            'answers',
            CollectionType::class,
            options: [
                'label' => false,
                'entry_type' => AnswerType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ]
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Question::class,
            ],
        );
    }

    protected function getDefaultDomain(): string
    {
        return self::$TRANSLATION_DOMAIN;
    }
}