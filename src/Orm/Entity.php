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
            $data = get_object_vars($data);
        }

        foreach ($data as $property => $value) {
            if (property_exists($this, 'guard') && in_array($property, $this->guard)) continue;
            property_exists($this, $property) && $this->{$property} = $value;
        }
        return $this;
    }

    public function guard(string $property)
    {
        $this->guard[] = $property;
    }

    public function unguard(string $property)
    {
        unset($this->guard[$property]);
        foreach ($this->guard as $idx => $prop) {
            if ($prop == $property) {
                unset($this->guard[$idx]);
                break;
            }
        }
    }

    private function getPrimaryKey(): string
    {
        return property_exists($this, 'primaryKey') ? $this->primarykey : 'id';
    }

    private function getPublicProperties(): array
    {
        $me = new class {
            function getProperties($object): array
            {
                return get_object_vars($object);
            }
        };
        return array_keys($me->getProperties($this));
    }

    public function save()
    {
        $this->validate();
        method_exists($this, 'beforeSave') && $this->beforeSave($this);

        $properties = [];

        foreach ($this->getPublicProperties() as $property) {
            $properties[$property] = $this->{$property};
        }

        $primaryKey = $this->getPrimaryKey();

        if (isset($this->{$primaryKey})) {
            $this->update([
                'data' => $properties,
                'where' => [
                    $primaryKey => $this->{$primaryKey}
                ]
            ]);
        } else {
            $user = $this->create([
                'data' => $properties
            ]);

            $this->fill($user);
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

    public function bind(string $key, mixed $value): self|null
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