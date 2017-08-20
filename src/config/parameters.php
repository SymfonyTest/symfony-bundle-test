<?php

if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 30300) {
    $container->loadFromExtension('framework', [
        'annotations' => ['enabled' => false],
    ]);
}
