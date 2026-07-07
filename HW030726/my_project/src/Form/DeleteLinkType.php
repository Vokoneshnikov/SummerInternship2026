<?php

namespace App\Form;

use App\Entity\Links;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('delete', SubmitType::class, [
                'label' => 'Удалить',
                'attr' => [
                    'class' => 'btn btn-danger btn-sm',
                    'onclick' => 'return confirm("Вы уверены, что хотите удалить эту ссылку?")'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Links::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'delete_link',
        ]);
    }
}
