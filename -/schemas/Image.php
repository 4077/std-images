<?php namespace std\images\schemas;

class Image extends \Schema
{
    public $table = 'std_images';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->morphs('imageable');
            $table->string('instance')->default('');
            $table->integer('position')->default(0);
            $table->boolean('enabled')->default(false);
        };
    }
}
