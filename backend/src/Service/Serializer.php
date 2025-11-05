<?php

namespace App\Service;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class Serializer
{
    private static ?SymfonySerializer $serializer = null;

    public static function instance(): SymfonySerializer
    {
        if (self::$serializer) {
            return self::$serializer;
        }

        self::$serializer = new SymfonySerializer([
            new DateTimeNormalizer(),
            new BackedEnumNormalizer(),
            new BackedEnumNormalizer(),
            new ObjectNormalizer(
                new ClassMetadataFactory(new AttributeLoader()),
                null,
                null,
                null,
                null,
                null,
                [AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                    return method_exists($object, 'getId') ? $object->getId() : spl_object_hash($object);
                }]
            ),
        ]);

        return self::$serializer;
    }

    /**
     * @throws ExceptionInterface
     */
    public static function normalize(mixed $data, ?string $format = null, array $context = []): mixed
    {
        return self::instance()->normalize($data, $format, $context);
    }

    public static function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return self::instance()->denormalize($data, $type, $format, $context);
    }
}
