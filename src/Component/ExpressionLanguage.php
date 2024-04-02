<?php

namespace EntityImporterBundle\Component;

use Symfony\Component\ExpressionLanguage as SymfonyExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

class ExpressionLanguage extends SymfonyExpressionLanguage
{
    private const AVAILABLE_PHP_FUNCTIONS = [
        'strtoupper',
        'ucwords',
    ];

    public function __construct()
    {
        parent::__construct();

        foreach (self::AVAILABLE_PHP_FUNCTIONS as $functionName) {
            $this->addFunction(ExpressionFunction::fromPhp($functionName));
        }
    }
}