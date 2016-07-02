<?php

namespace Tequilla\MongoDB;

use Symfony\Component\OptionsResolver\OptionsResolver;

class Client implements ClientInterface
{
    protected $optionsResolver;

    public function __construct($uri, array $options = [], array $driverOptions = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined([
            
        ]);
    }
}