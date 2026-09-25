<?php

declare(strict_types=1);

namespace Agenciafmd\Support\PHPStan;

use Agenciafmd\Support\Faker\Provider;
use Faker\Generator;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;

/**
 * Expõe no `Faker\Generator` os métodos públicos do nosso `Provider`, registrado em runtime via `addProvider()`.
 */
final readonly class FakerProviderMethodsExtension implements MethodsClassReflectionExtension
{
    public function __construct(private ReflectionProvider $reflectionProvider) {}

    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if (! $classReflection->is(Generator::class)) {
            return false;
        }

        $provider = $this->reflectionProvider->getClass(Provider::class);

        if (! $provider->hasNativeMethod($methodName)) {
            return false;
        }

        $method = $provider->getNativeMethod($methodName);

        return $method->isPublic()
            && ! $method->isStatic()
            && $method->getDeclaringClass()->getName() === Provider::class;
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return $this->reflectionProvider
            ->getClass(Provider::class)
            ->getNativeMethod($methodName);
    }
}
