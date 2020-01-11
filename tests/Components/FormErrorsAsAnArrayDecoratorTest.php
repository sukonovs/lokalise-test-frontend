<?php

namespace App\Tests\Components;

use App\Components\FormErrorsAsAnArrayDecorator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;

class FormErrorsAsAnArrayDecoratorTest extends KernelTestCase
{
    public function testCorrectRepresentationOfMessages()
    {
        self::bootKernel();

        $formFactory = self::$container->get(FormFactoryInterface::class);

        $form = $formFactory->createNamed('root');
        $childForm = $formFactory->createNamed(
            'child',
            FormType::class,
            null,
            ['auto_initialize' => false, 'error_bubbling' => false]
        );
        $form->add($childForm);

        $form->addError(new FormError('error1'));
        $form->addError(new FormError('error2'));
        $childForm->addError(new FormError('error3'));

        $expectedErrors = [
            0 => 'error1',
            1 => 'error2',
            'child' => [
                0 => 'error3',
            ],
        ];

        $decorator = new FormErrorsAsAnArrayDecorator($form);

        $this->assertEquals($expectedErrors, $decorator->getErrors());
    }
}
