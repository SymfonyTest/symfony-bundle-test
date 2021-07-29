<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $container->extension('configuration', [
        'foo' => 'val1',
        'bar' => ['val2', 'val3'],
    ]);
};
