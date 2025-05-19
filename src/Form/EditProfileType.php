<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class EditProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d’utilisateur',
            ])
            ->add('email', TextType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'required' => false,
                'mapped' => false,
            ])
            ->add('avatarUrl', TextType::class, [
                'label' => 'URL de l’avatar',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Mettre à jour mes informations',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

