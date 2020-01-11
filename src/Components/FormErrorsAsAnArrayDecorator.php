<?php

namespace App\Components;

use Symfony\Component\Form\FormInterface;

class FormErrorsAsAnArrayDecorator
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @param FormInterface $form
     */
    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        $errors = [];

        foreach ($this->form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($this->form->all() as $form) {
            if ($form->getErrors()) {
                $childForDecorator = new FormErrorsAsAnArrayDecorator($form);
                $errors[$form->getName()] = $childForDecorator->getErrors();
            }
        }

        return $errors;
    }
}
