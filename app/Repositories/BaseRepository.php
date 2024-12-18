<?php

namespace App\Repositories;


use Illuminate\Contracts\Container\BindingResolutionException;

abstract class BaseRepository
{
    protected $model;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     * get Model
     */
    abstract public function getModel();

    /**
     * set Model
     * @throws BindingResolutionException
     */
    public function setModel(): void
    {
        $this->model = app()->make($this->getModel());
    }

    public function getAll()
    {
        return $this->model->orderBy('id', 'DESC')->get();
    }

    public function paginate($perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    public function create($object)
    {
        return $object->save();
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function update($object)
    {
        return $object->save();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findBy($field, $value)
    {
        return $this->model->where($field, $value)->orderBy('created_at', 'DESC')->paginate(15);
    }

    public function findByNotPagination($field, $value)
    {
        return $this->model->where($field, $value)->orderBy('created_at', 'DESC')->get();
    }

    public function findByClauses(array $data)
    {
        $model = $this->model;
        foreach ($data as $d) {
            $model = $model->where($d['field'], $d['operator'], $d['value']);
        }
        return $model->orderBy('created_at', 'DESC')->paginate(15);
    }

    public function findByClausesNotPagination(array $data)
    {
        $model = $this->model;
        foreach ($data as $d) {
            $model = $model->where($d['field'], $d['operator'], $d['value']);
        }
        return $model->orderBy('created_at', 'DESC')->get();
    }
}
