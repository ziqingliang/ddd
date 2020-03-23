<?php

namespace {{factoryNamespace}};


use ziqing\ddd\Factory;
use ziqing\ddd\base\who\Who;
use ziqing\ddd\Exceptions\LookupFailed;
use {{modelFullClassName}};
use {{entityFullClassName}};

/**
 * Class {{factoryClassName}}
 * @package {{package}}
 * @internal
 *
 * 用于构造实体 {{entityClassName}} 的类实例
 *
 */
class {{factoryClassName}} extends Factory
{
    /**
     * you can define your dependency here
     * {{factoryClassName}} constructor.
     */
    public function __construct()
    {
    }

    public function init()
    {
        //you can init something here
    }
    
    /**
     * @param {{modelClassName}} $model
     * @return {{entityClassName}}
     * @throws LookupFailed
     */
    public function buildOne({{modelClassName}} $model): {{entityClassName}}
    {
        $names = [{{fields}}];
        $data = ['id'=>$model->id];
        foreach ($names as $name){
            $data[$name] = $model->$name;
        }

        $data['createdAt'] = $model->createdAt;
        $data['updatedAt'] = $model->updatedAt;

        $createdBy = $model->createdBy ? json_decode($model->createdBy, true) : null;
        $updatedBy = $model->updatedBy ? json_decode($model->updatedBy, true) : null;

        $data['creator'] = $createdBy ? Who::getById($createdBy['id'], $createdBy['name']) : null;
        $data['updater'] = $updatedBy ? Who::getById($updatedBy['id'], $updatedBy['name']) : null;

        $entity = new {{entityClassName}}($data);

{{entityFields}}

        return $entity;
    }

    /**
     * @param {{modelClassName}}[] $models
     * @return {{entityClassName}}[]
     * @throws LookupFailed
     */
    public function buildMany(array $models): array
    {
        $list = [];
        foreach ($models as $model){
            $list[] = $this->buildOne($model);
        }
        return $list;
    }
}
