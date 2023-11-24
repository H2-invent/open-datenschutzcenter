<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 17.09.2019
 * Time: 20:29
 */

namespace App\Form\Type;

use App\Entity\AkademieKurse;
use App\Entity\Questionnaire;
use App\Entity\Team;
use App\Form\Type\Template\BaseType;
use App\Repository\QuestionnaireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KursType extends BaseType
{
    private static string $TRANSLATION_DOMAIN = 'academy';
    private static string $BASE_LABEL = 'lesson.';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            TextType::class,
            $this->getOptions('title', domain: 'general'),
        );
        $builder->add(
            'video',
            TextType::class,
            $this->getOptions(self::$BASE_LABEL . 'video.link'),
        );

        $builder->add(
            'type',
            ChoiceType::class,
            $this->getOptions(
                label: self::$BASE_LABEL . 'video.type',
                additionalOptions: [
                    'choices' => [
                        'Lokal/Cloud Storage oder CDN' => 0,
                        'Vimeo' => 1,
                    ],
                    'multiple' => false,
                    'attr' => [
                        'class' => 'selectpicker',
                    ],
                ],
            ),
        );
        $builder->add(
            'beschreibung',
            TextareaType::class,
            $this->getOptions(
                label: 'description',
                domain: 'general',
                additionalOptions: [
                    'attr' => ['rows' => 12],
                ],
            ),
        );
        $builder->add(
            'questionnaire',
            EntityType::class,
            $this->getOptions(
                label: 'questionnaire.word',
                additionalOptions: [
                    'class' => Questionnaire::class,
                    'choice_label' => 'label',
                    'query_builder' => function (QuestionnaireRepository $repo) use ($options) {
                        return $repo->createQueryBuilder('q')
                            ->where('q.team = :team')
                            ->setParameter('team', $options['data']->getTeam());
                    }
                ]
            ),
        );
        $builder->add(
            'save',
            SubmitType::class,
            [
                'label' => 'save',
                'translation_domain' => 'general',
                'attr' => ['class' => 'btn'],
            ],
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AkademieKurse::class,
            'team' => new Team(),
        ]);
    }

    protected function getDefaultDomain(): string
    {
        return self::$TRANSLATION_DOMAIN;
    }
}
