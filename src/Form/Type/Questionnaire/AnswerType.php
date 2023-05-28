<?php
declare(strict_types=1);

namespace App\Form\Type\Questionnaire;

use App\Entity\Answer;
use App\Form\Type\Template\BaseType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnswerType extends BaseType
{
    private static string $TRANSLATION_DOMAIN = 'questionnaire';
    private static string $BASE_LABEL = 'answer.';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'label',
            TextType::class,
            $this->getOptions(self::$BASE_LABEL . 'label'),
        );

        $builder->add(
            'isCorrect',
            CheckboxType::class,
            $this->getOptions(self::$BASE_LABEL . 'isCorrect', false),
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Answer::class,
            ],
        );
    }

    protected function getDefaultDomain(): string
    {
        return self::$TRANSLATION_DOMAIN;
    }
}