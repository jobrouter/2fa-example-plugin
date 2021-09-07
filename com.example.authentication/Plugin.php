<?php

namespace Example\Authentication;

use JobRouter\Plugin\ExtensionRegistry;
use JobRouter\Plugin\ExtensionTypeOperator;
use JobRouter\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    public function load(ExtensionRegistry $registry): void
    {
        $operator = new ExtensionTypeOperator($registry);

        $operator->registerTranslations(__DIR__ . '/languages');

        $operator->registerAuthenticationFactor(
            'example_authenticator',
            'Example\\Authentication\\Factor\\ExampleAuthenticator'
        );
    }
}
