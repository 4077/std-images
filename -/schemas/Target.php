<?php namespace std\images\schemas;

class Target extends \Schema
{
    public $table = 'std_images_targets';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('name')->default('');
            $table->longtext('cache');
        };
    }
}
