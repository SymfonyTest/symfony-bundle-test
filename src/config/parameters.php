<?php

// If sf 3.3 and no annotations is installed, then disable it.
if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 30300 && !class_exists('Doctrine\Common\Annotations\Annotation')) {
    $container->loadFromExtension('framework', [
        'annotations' => ['enabled' => false],
    ]);
} else {
    $container->loadFromExtension('framework', [
        'annotations' => ['cache' => 'none'],
    ]);
}

// Not setting the router to utf8 is deprecated in symfony 5.1
if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 50100) {
    $container->loadFromExtension('framework', [
        'router' => ['utf8' => true],
    ]);
}
