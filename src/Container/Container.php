<?php

namespace Pexess\Container;

use Pexess\Exceptions\ContainerException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{

    private array $entries = [];

    public function get(string $id)
    {
        if ($this->has($id)) {
            $entry = $this->entries[$id];
            return $entry();
        }

        return $this->resolve($id);
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    public function set(string $id, callable $concrete): void
    {
        $this->entries[$id] = $concrete;
    }

    public function resolve(string $id)
    {
        $reflectionClass = new \ReflectionClass($id);

        if (!$reflectionClass->isInstantiable()) {
            throw new ContainerException("Class $id is not instantiable");
        }

        $constructor = $reflectionClass->getConstructor();

        if (!$constructor) {
            return new $id();
        }

        $parameters = $constructor->getParameters();

        if (!$parameters) {
            return new $id();
        }

        $dependencies = $this->resolveParameters($parameters);

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    public function resolveParameters(array $parameters): array
    {
        return array_map(function (\ReflectionParameter $param) {
            $name = $param->getName();
            $type = $param->getType();

            if (!$type) {
                throw new ContainerException("Param $name is missing a type hint");
            }

            if ($type instanceof \ReflectionUnionType) {
                throw new ContainerException("Failed to resolve because of union type for param $name");
            }

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                return $this->get($type->getName());
            }

            throw new ContainerException(
                'Invalid param "' . $name . '"'
            );
        },
            $parameters
        );
    }

    public function call(callable $callable)
    {
        $reflector = new \ReflectionFunction($callable);
        $parameters = $reflector->getParameters();

        $dependencies = $this->resolveParameters($parameters);

        return $reflector->invoke(...$dependencies);
    }

    public function make(object $object, string $method)
    {
        $reflector = new \ReflectionMethod($object, $method);
        $parameters = $reflector->getParameters();

        $dependencies = $this->resolveParameters($parameters);

        return $reflector->invoke($object, ...$dependencies);
    }
}