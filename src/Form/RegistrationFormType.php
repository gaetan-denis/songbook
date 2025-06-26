<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null, [
                'label' => 'Nom d\'utilisateur',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un nom d\'utilisateur',
                    ]),
                ]

            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez saisir votre mot de passe',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                            'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule et un chiffre.',

                        ])
                    ],
                    ],
                'second_options' => ['label' => 'Confirmer le mot de passe'],
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'attr' => ['autocomplete' => 'new-password'],
            ])
            ->add('agreeTerms', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'J\'accepte la <a href="/privacy-policy" target="_blank">politique de confidentialité</a> et les <a href="/term_of_use" target="_blank">conditions d\'utilisation</a>',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\IsTrue([
                        'message' => 'Vous devez accepter la politique de confidentialité et les conditions d\'utilisation pour vous inscrire.',
                    ]),
                ],
                'label_html' => true,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
