<?php

namespace App\Repositories\Core;

interface IRepository
{
    public function all($columns = array('*'));

    public function paginate($perPage = 15, $columns = array('*'));

    public function create(array $data);

    public function update(array $data, $id);

    public function find($id, $columns = array('*'));

    public function findBy($field, $value, $columns = array('*'));
}