<?php

namespace Project\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class AttachmentForm extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File('attachment');
        $file->setLabel('Attach file')
            ->setAttribute('id', 'attachment');
        $this->add($file);
    }
}