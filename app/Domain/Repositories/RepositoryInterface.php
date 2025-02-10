<?php

namespace App\Domain\Repositories;

interface RepositoryInterface
{
    public function getAll();

    public function paginate($perPage = 15);

    public function create($object);

    public function delete($id);

    public function update($object);

    public function find($id);

    public function findBy($field, $value);

    public function findByNotPagination($field, $value);

    public function findByClauses(array $data);

    public function findByClausesNotPagination(array $data);
}
