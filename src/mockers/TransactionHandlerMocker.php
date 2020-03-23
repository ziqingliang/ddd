<?php
/**
 * Created by PhpStorm.
 * User: ziqing
 * Date: 2018/5/31
 * Time: 上午12:44
 */

namespace ziqing\ddd\mockers;


use ziqing\ddd\contracts\TransactionHandlerInterface;
use ziqing\ddd\Exceptions\TransactionFailed;

class TransactionHandlerMocker implements TransactionHandlerInterface
{
    /**
     * @return void
     * @throws TransactionFailed
     */
    public function begin()
    {
        //nothing here
    }

    /**
     * @return void
     * @throws TransactionFailed
     */
    public function commit()
    {
        //nothing here
    }

    /**
     * @return void
     * @throws TransactionFailed
     */
    public function rollback()
    {
        //nothing here
    }
}
