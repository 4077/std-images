<?php namespace std\images;

class Target
{
    public static $targets = [];

    public static function get($name)
    {
        if (!isset(static::$targets[$name])) {
            if (!$target = \std\images\models\Target::where('name', $name)->first()) {
                $target = \std\images\models\Target::create(['name' => $name]);
            }

            static::$targets[$name] = $target;
        }

        return static::$targets[$name];
    }

    public static function getExists($name)
    {
        if (!isset(static::$targets[$name])) {
            static::$targets[$name] = \std\images\models\Target::where('name', $name)->first();
        }

        return static::$targets[$name];
    }

    public static function rename($name, $newName)
    {
        if ($target = static::getExists($name)) {
            if (!static::getExists($newName)) {
                $target->name = $newName;
                $target->save();

                return true;
            }
        }
    }
}
