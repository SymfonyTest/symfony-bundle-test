<?php

// If annotations aren't installed, disable it.
if (!class_exists('Doctrine\Common\Annotations\Annotation')) {
    $container->loadFromExtension('framework', [
        'annotations' => ['enabled' => false],
    ]);
} elseif (method_exists('Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension', 'registerAnnotationsConfiguration')) {
    $container->loadFromExtension('framework', [
        'annotations' => ['cache' => 'none'],
    ]);
}
