<?php

namespace Melody\AdminBundle\Twig\Loader;

class FilesystemLoader extends \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader {
    
    protected function validateName($name)
    {
        if ( strpos($name,'../CommonAdmin') === 0 ) {
            return;
        }
        parent::validateName($name);
    }
}