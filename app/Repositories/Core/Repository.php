<?php
namespace App\Repositories\Core;

use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Exception;

abstract class Repository implements IRepository
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @param App $app
     * @throws Exception
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed|string
     */
    abstract function model();

    /**
     * @param array $columns
     * @return Collection
     */
    public function all($columns = array('*')): Collection
    {
        return $this->model->newQuery()->get($columns);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 15, $columns = array('*'))
    {
        return $this->model->newQuery()->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public function create(array $data)
    {
        return $this->model->newQuery()->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return int
     */
    public function update(array $data, $id, $attribute = "id")
    {
        return $this->model->newQuery()->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function find($id, $columns = array('*'))
    {
        return $this->model->newQuery()->find($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder|Model|null|object
     */
    public function findBy($attribute, $value, $columns = array('*'))
    {
        return $this->model->newQuery()->where($attribute, '=', $value)->first($columns);
    }

    /**
     * @throws Exception
     */
    public function makeModel()
    {
        try {
            $model = $this->app->make($this->model());

            if (!$model instanceof Model) {
                throw new Exception("Class {$this->model()} must be an instance of {Illuminate\\Database\\Eloquent\\Model}");
            }
            $this->model = $model;
        } catch (Exception $e) {
            \Log::error($e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile());
            throw new Exception($e->getMessage());
        }
    }
}