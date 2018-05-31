<?php
/**
 * Created by PhpStorm.
 * User: lanzhi
 * Date: 2018/5/30
 * Time: 下午8:00
 */

namespace lanzhi\ddd\contracts;


use lanzhi\ddd\Exceptions\TransactionFailed;

interface TransactionHandlerInterface
{
    /**
     * @return void
     * @throws TransactionFailed
     */
    public function begin();

    /**
     * @return void
     * @throws TransactionFailed
     */
    public function commit();

    /**
     * @return void
     * @throws TransactionFailed
     */
    public function rollback();
}