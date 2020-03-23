<?php

namespace {{repositoryNamespace}};


use ziqing\ddd\Process;
use ziqing\ddd\Value;
use ziqing\ddd\Entity;
use ziqing\ddd\Repository;
use ziqing\ddd\Exceptions\AddFailed;
use ziqing\ddd\Exceptions\UnknownCondition;
use ziqing\ddd\Exceptions\UpdateFailed;
use ziqing\ddd\base\conditions\IdCondition;
use ziqing\ddd\base\conditions\GetAllCondition;
use ziqing\ddd\base\conditions\IdsCondition;
use ziqing\ddd\base\conditions\PairCondition;
use ziqing\ddd\base\conditions\PairsCondition;
use ziqing\ddd\base\conditions\InCondition;
use Illuminate\Database\Eloquent\Builder;
use ziqing\ddd\Exceptions\UnSupported;
use {{entityFullClassName}};
use {{modelFullClassName}};
use {{factoryFullClassName}};

/**
 * Class {{repositoryClassName}}
 * @package {{package}}
 *
 * 实体 {{entityClassName}} 资源管理器
 */
class {{repositoryClassName}} extends Repository
{
    /**
     * @var {{factoryClassName}}
     */
    private $factory;
    /**
     * @var {{modelClassName}}
     */
    private $model;
    /**
     * @var array 可供计数的属性集合
     */
    private $sums = [];

    /**
     * you can define your dependency here
     * {{repositoryClassName}} constructor.
     * @param {{factoryClassName}} $factory
     * @param {{modelClassName}} $model
     */
    public function __construct({{factoryClassName}} $factory, {{modelClassName}} $model)
    {
        $this->factory = $factory;
        $this->model   = $model;
    }

    public function init()
    {
        //you can init something here
    }

    /**
     * @return Builder
     * @throws UnknownCondition
     */
    private function getBuilderWithCondition()
    {
        /**
         * @var IdCondition|IdsCondition|PairCondition|PairsCondition|InCondition $condition
         */
        $conditions = $this->getConditions();
        $builder    = $this->model::query();

        foreach($conditions as $condition){
            switch (get_class($condition)){
                case GetAllCondition::class:
                    break;
                case IdCondition::class:
                    $builder->where('id', $condition->getId());
                    break;
                case IdsCondition::class:
                    $builder->whereIn('id', $condition->getIds());
                    break;
                case InCondition::class:
                    $builder->whereIn($condition->getName(), $condition->getSet());
                    break;
                case PairCondition::class:
                    $builder->where($condition->getName(), $condition->getValue());
                    break;
                case PairsCondition::class:
                    $builder->where($condition->getPairs());
                    break;

                //todo: case condition1
                //todo: case condition2
                //... ...
                //todo: case conditionN

                default:
                    throw new UnknownCondition("不支持过滤条件:".get_class($condition));
                    break;
            }
        }

        $builder->where('deletedAt', null);

        return $builder;
    }

    /**
     * 具体实现持久化一个新实体
     * @param Entity $entity
     * @return int 返回自增ID
     * @throws AddFailed
     */
    protected function _add(Entity $entity): int
    {
        /**
         * @var {{entityClassName}} $entity
         */
        $model = clone $this->model;

{{entityToModelLogicCode}}

        $model->createdAt = $entity->createdAt ? $entity->createdAt : "";
        $model->updatedAt = $entity->updatedAt ? $entity->updatedAt : "";
        $model->creatorId = $entity->creator ? $entity->creator->id : 0;
        $model->updaterId = $entity->updater ? $entity->updater->id : 0;
        $model->createdBy = $entity->creator ? json_encode($entity->creator->toArray(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES): "";
        $model->updatedBy = $entity->updater ? json_encode($entity->updater->toArray(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES): "";

        if(!$model->save()){
            throw new AddFailed(json_encode($entity->toArray(), JSON_UNESCAPED_UNICODE));
        }
        return $model->id;
    }

    /**
     * 具体实现持久化一个已存在实体
     * @param Entity $entity
     * @return true
     * @throws UpdateFailed
     */
    protected function _update(Entity $entity): bool
    {
        /**
         * @var {{entityClassName}} $entity
         * @var {{modelClassName}} $model
         */
        $model = $this->model::query()->find($entity->id);
        if(!$this->isRecordValid($model->deletedAt, $model->deletedBy)){
            //实体已经被删除，抛出异常
            throw new UpdateFailed("实体已经被移除; entityId:{$entity->id}; deletedAt:{$model->deletedAt}");
        }

{{entityToModelLogicCode}}

        $model->createdAt = $entity->createdAt ? $entity->createdAt : "";
        $model->updatedAt = $entity->updatedAt ? $entity->updatedAt : "";
        $model->creatorId = $entity->creator ? $entity->creator->id : 0;
        $model->updaterId = $entity->updater ? $entity->updater->id : 0;
        $model->createdBy = $entity->creator ? json_encode($entity->creator->toArray(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES): "";
        $model->updatedBy = $entity->updater ? json_encode($entity->updater->toArray(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES): "";

        if(!$model->save()){
            throw new UpdateFailed(json_encode($entity->toArray(), JSON_UNESCAPED_UNICODE));
        }
        return true;
    }

    /**
     * 具体实现从持久化中移除一个已存在实体
     * 如果一个实体本身是未被持久化即无效的，则直接忽略，此类情况不应该抛出异常
     * @param Entity $entity
     * @return true
     */
    protected function _remove(Entity $entity): bool
    {
        /**
         * @var {{entityClassName}} $entity
         * @var {{modelClassName}} $model
         */
        $model = $this->model::query()->find($entity->id);
        if(!$model || !$this->isRecordValid($model->deletedAt, $model->deletedBy)){
            //此时实体不存在或者已经被移除，不报错，直接返回true
            return true;
        }
        //移除实体
        $model->deletedAt = date('Y-m-d H:i:s');
        $model->deleterId = Process::whoAmI()->id;
        $model->deletedBy = json_encode(Process::whoAmI()->toArray(), JSON_UNESCAPED_UNICODE);
        return $model->save();
    }

    /**
     * 具体实现从存储中得到一个实体
     * @return {{entityClassName}}[]
     * @throws UnknownCondition
     * @throws \Exception
     */
    protected function _get(): array
    {
        $builder = $this->getBuilderWithCondition();

        $builder->offset($this->getFrom())->limit($this->getLength());

        $this->getOrderBy()  && $builder->orderBy(...$this->getOrderBy());
        $this->getGroupBys() && $builder->groupBy(...$this->getGroupBys());

        $models = $builder->getModels();

        $entities = $this->factory->buildMany($models);

        return $entities;
    }

    /**
     * 具体实现方法
     * @param {{entityClassName}}[] $entities
     * @return int[]
     * @throws AddFailed
     */
    protected function _addMany(array $entities): array
    {
        $ids = [];
        foreach ($entities as $entity){
            $ids[] = $this->_add($entity);
        }

        return $ids;
    }

    /**
     * 具体实现方法
     * 根据筛选条件单次更新多个实体
     * 此时无法通过观察者模式追踪实体变化
     * @param array $data
     * @return void
     * @throws UnknownCondition
     */
    protected function _updateManyWithCondition(array $data)
    {
        $builder = $this->getBuilderWithCondition();
        $builder->update($data);
    }

    /**
     * 具体实现方法
     * 根据筛选条件单次更新多个实体
     * 此时无法通过观察者模式追踪实体变化
     * @return void
     * @throws UnknownCondition
     */
    protected function _removeMany()
    {
        $builder = $this->getBuilderWithCondition();
        $builder->update([
            'deletedAt' => date('Y-m-d H:i:s'),
            'deleterId' => Process::whoAmI()->id,
            'deletedBy' => json_encode(Process::whoAmI()->toArray(), JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * 具体实现从存储中计算当前条件下实体的个数
     * @return int
     * @throws UnknownCondition
     */
    protected function _size(): int
    {
        $builder = $this->getBuilderWithCondition();
        return $builder->count();
    }

    protected function _sum(string $name): int
    {
        if(empty($this->sums[$name])){
            throw new Unsupported("can't sum $name");
        }

        $builder = $this->getBuilderWithCondition();
        return $builder->sum($name);
    }

    private function isRecordValid($deletedAt, $deletedBy)
    {
        return $deletedAt=='' && $deletedBy=='';
    }

    /**
     * @param Value[] $values
     * @return string
     */
    private static function encodeValues(array $values)
    {
        $list = [];
        foreach ($values as $value){
            $list[] = $value->toArray();
        }
        return json_encode($list, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param Entity[] $entities
     * @return string
     */
    private static function encodeEntities(array $entities)
    {
        $ids = [];
        foreach ($entities as $entity){
            assert($entity->isValid());
            $ids[] = $entity->id;
        }
        return json_encode($ids);
    }

}
