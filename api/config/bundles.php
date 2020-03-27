<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class                    => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class     => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class                      => ['all' => true],
    Symfony\Bundle\MercureBundle\MercureBundle::class                        => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class                              => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class                     => ['all' => true],
    ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle::class          => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class                                => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class                => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class                            => ['dev' => true],
    Conduction\CommonGroundBundle\CommonGroundBundle::class                  => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class        => ['all' => true],
    Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class             => ['all' => true],
    Tbbc\MoneyBundle\TbbcMoneyBundle::class                                  => ['all' => true],
    Knp\Bundle\MarkdownBundle\KnpMarkdownBundle::class                       => ['all' => true],
];
