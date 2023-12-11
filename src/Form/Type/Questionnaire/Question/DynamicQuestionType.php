<?php
declare(strict_types=1);

namespace App\Form\Type\Questionnaire\Question;

use App\DataTypes\QuestionTypes;
use App\Entity\Question;
use App\Form\Type\Template\BaseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DynamicQuestionType extends BaseType
{
    protected function getDefaultDomain(): string
    {
        return '';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $question = $options['question'];
        $answers = $question->getAnswers()->toArray();
        shuffle($answers);


        $builder->add(
            (string)$question->getId(),
            ChoiceType::class,
            [
                'label' => $question->getHint() ?? false,
                'required' => true,
                'expanded' => true,
                'choices' => $answers,
                'choice_value' => 'id',
                'choice_label' => 'label',
                'multiple' => $question->getType() === QuestionTypes::CHECKBOX,
            ],
        );

        $builder->add(
            'continue',
            SubmitType::class,
            [
                'attr' => [
                    'class' => 'btn'
                ],
                'label' => 'continue',
                'translation_domain' => 'general',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'question' => new Question(),
            ],
        );
    }
}