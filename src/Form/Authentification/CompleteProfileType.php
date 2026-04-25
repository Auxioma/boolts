<?php

namespace App\Form\Authentification;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CompleteProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-16',
                    'placeholder' => 'Veuillez entrer votre nom',
                ]
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control mb-16',
                    'placeholder' => 'Veuillez entrer votre prénom',
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],

                'first_options' => [
                    'label' => 'form.password.new',
                    'attr' => [
                        'class' => 'form-control mb-16',
                        'placeholder' => 'Veuillez entrer votre mot de passe',
                    ],
                    'constraints' => [
                        new NotBlank(
                            message: 'form.password.error.blank'
                        ),
                        new Length(
                            min: 8,
                            max: 4096,
                            minMessage: 'form.password.error.min',
                            maxMessage: 'form.password.error.max',
                        ),
                        /*new PasswordStrength(
                            message: 'form.password.error.weak'
                        ),
                        new NotCompromisedPassword(
                            message: 'form.password.error.compromised'
                        ),*/
                    ],
                ],

                'second_options' => [
                    'label' => 'form.password.repeat',
                    'attr' => [
                        'placeholder' => 'form.password.placeholder.repeat',
                        'class' => 'form-control mb-32',
                    ],
                ],

                'invalid_message' => 'form.password.error.mismatch',
                'mapped' => false,
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'attr' => [
                    'class' => '"form-check-input',
                ],
                'mapped' => false,
                'label' => 'form.terms.label',
                'constraints' => [
                    new IsTrue(
                        message: 'form.terms.error.required'
                    ),
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
