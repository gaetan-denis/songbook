<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('email', EmailType::class)
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => function ($role) {
                    return match($role->getName()) {
                        'ROLE_ADMIN' => 'Administrateur',
                        'ROLE_USER' => 'Utilisateur',
                        'ROLE_MODERATOR' => 'Modérateur',
                        default => $role->getName(),
                    };
                },
                'label' => 'Rôle',
                'placeholder' => 'Sélectionnez un rôle',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
