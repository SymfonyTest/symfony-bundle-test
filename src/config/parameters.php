<?php

// If sf 3.3 and no annotations is installed, then disable it.
if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 30300 && !class_exists('Doctrine\Common\Annotations\Annotation')) {
    $container->loadFromExtension('framework', [
        'annotations' => ['enabled' => false],
    ]);
}
