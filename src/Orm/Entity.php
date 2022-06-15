<?php

namespace Pexess\Orm;

use Pexess\Exceptions\HttpException;
use Pexess\Exceptions\NotFoundException;
use Pexess\Helpers\StatusCodes;
use Pexess\Validator\Validator;

abstract class Entity extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct($this->table);
    }

    public function fill(array|object $data): self
    {
        if (is_object($data)) {
            $data = $this->getPublicProperties($data);
        }

        foreach ($data as $property => $value) {
            if (property_exists($this, 'guard') && in_array($property, $this->guard)) continue;
            property_exists($this, $property) && $this->{$property} = $value;
        }
        return $this;
    }

    public function guard(string $property): self
    {
        foreach (func_get_args() as $prop) {
            $this->guard[] = $prop;
        }
        return $this;
    }

    public function unguard(string $property): self
    {
        $property = func_get_args();
        foreach ($this->guard as $idx => $prop) {
            if (in_array($prop, $property)) {
                unset($this->guard[$idx]);
            }
        }
        return $this;
    }

    private function getPrimaryKey(): string
    {
        return property_exists($this, 'primaryKey') ? $this->primarykey : 'id';
    }

    private function getPublicProperties(object $object): array
    {
        $me = new class {
            function getProperties(object $object): array
            {
                return get_object_vars($object);
            }
        };
        return $me->getProperties($object);
    }

    public function save()
    {
        method_exists($this, 'beforeSave') && $this->beforeSave($this);

        $properties = $this->getPublicProperties($this);

        $primaryKey = $this->getPrimaryKey();

        if (isset($this->{$primaryKey})) {
            $this->update([
                'data' => $properties,
                'where' => [
                    $primaryKey => $this->{$primaryKey}
                ]
            ]);
        } else {
            $entity = $this->create([
                'data' => $properties
            ]);

            $this->fill($entity);
        }

        method_exists($this, 'afterSave') && $this->afterSave($this);
    }

    public function destroy(): void
    {
        $primaryKey = $this->getPrimaryKey();
        $this->delete([
            'where' => [
                $primaryKey => $this->{$primaryKey}
            ]
        ]);
    }

    public function findWhere(string $key, mixed $value): self|null
    {
        $entity = $this->findUnique([
            'where' => [
                $key => $value
            ]
        ]);

        if (!$entity) {
            method_exists($this, 'fallback') ? $this->fallback() : throw new NotFoundException();
            exit();
        }

        $this->fill($entity);

        return $this;

    }

    public function validate(): void
    {
        $rules = method_exists($this, 'rules') ? $this->rules() : [];
        $errors = Validator::validate((array)$this, $rules);
        if ($errors) {
            method_exists($this, 'onValidationFailed') ?
                $this->onValidationFailed($errors) :
                throw new HttpException([
                    'statusCode' => StatusCodes::BAD_REQUEST,
                    'message' => 'Bad Request',
                    'errors' => $errors
                ], StatusCodes::BAD_REQUEST);
        }
    }

    protected array $guard = [];
}