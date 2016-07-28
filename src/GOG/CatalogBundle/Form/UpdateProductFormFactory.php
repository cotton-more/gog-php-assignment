<?php
/**
 * Created by PhpStorm.
 * User: inikulin
 * Date: 26/07/16
 * Time: 21:45
 */

namespace GOG\CatalogBundle\Form;


use Symfony\Component\Form\FormFactoryInterface;

class UpdateProductFormFactory
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $name;
    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param string               $type
     * @param string               $name
     */
    public function __construct(FormFactoryInterface $formFactory, $type, $name)
    {
        $this->formFactory = $formFactory;
        $this->type        = $type;
        $this->name        = $name;
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     */
    public function createForm()
    {
        $builder = $this->formFactory->createNamedBuilder(
            $this->name,
            $this->type,
            null,
            [ 'method' => 'PATCH' ]
        );

        return $builder->getForm();
    }
}