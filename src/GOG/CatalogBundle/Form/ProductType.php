<?php namespace GOG\CatalogBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    /**
     * @var string
     */
    private $productClass;

    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \GOG\CatalogBundle\Entity\Product $productClass */
        $productClass = $this->productClass;
        
        $builder->add('title', TextType::class);
        $builder->add('price', MoneyType::class);
        $builder->add('currency', ChoiceType::class, array(
            'choices' => $productClass::getCurrencyChoices(),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->productClass,
            'csrf_protection' => false,
        ));
    }
}