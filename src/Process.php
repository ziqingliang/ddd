<?php


namespace ziqing\ddd;


use ziqing\ddd\base\who\Who;

class Process
{
    private static $who;

    /**
     * @return Who
     */
    public static function whoAmI()
    {
        return self::$who ?? new Who();
    }

    /**
     * @param Who $who Who
     */
    public static function with(Who $who)
    {
        self::$who = $who;
    }
}
