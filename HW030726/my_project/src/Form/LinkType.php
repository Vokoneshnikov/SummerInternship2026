<?php

namespace App\Form;

use App\Entity\Links;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\GreaterThan;

class LinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldLink', UrlType::class, [
                'label' => 'Введите URL для сокращения',
                'attr' => [
                    'placeholder' => 'https://example.com/очень-длинная-ссылка',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите URL'
                    ]),
                    new Url([
                        'message' => 'Пожалуйста, введите корректный URL (например, https://example.com)',
                        'protocols' => ['http', 'https']
                    ])
                ]
            ])
            ->add('isDisposable', CheckboxType::class, [
                'label' => 'Сделать ссылку одноразовой?',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
            ->add('expiresAt', DateTimeType::class, [
                'label' => 'Дата устаревания',
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,  // Включаем HTML5
                'input' => 'datetime_immutable',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local',  // Явно указываем тип
                    'step' => '60',  // Шаг в минутах
                    'min' => (new \DateTime('+1 hour'))->format('Y-m-d\TH:i')  // Минимальная дата - через час
                ],
                'constraints' => [
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'Дата устаревания должна быть в будущем'
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Links::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'link_form',
        ]);
    }
}
