<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'label' => $this->translator->trans('product.title', [], 'messages')
            ])
            ->add('content', null, [
                'label' => $this->translator->trans('product.content', [], 'messages')
            ])
            ->add('price', null, [
                'label' => $this->translator->trans('product.price', [], 'messages')
            ])
            ->add('stock', null, [
                'label' => $this->translator->trans('product.stock', [], 'messages')
            ])
            ->add('image', FileType::class, [
                'label' => $this->translator->trans('product.image', [], 'messages'),
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => $this->translator->trans('product.invalid_image', [], 'messages'),
                    ])
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->translator->trans('account.my_account', [], 'messages')
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'locale' => 'en', // default locale
        ]);
    }
}
