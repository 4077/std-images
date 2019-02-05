<?php namespace std\images\schemas;

class Version extends \Schema
{
    public $table = 'std_images_versions';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('image_id')->default(0)->unsigned();
            $table->string('query')->default('');
            $table->char('md5', 32)->default('');
            $table->char('sha1', 40)->default('');
            $table->string('file_path')->default('');
            $table->integer('file_size')->default(0)->unsigned();
            $table->smallInteger('width')->default(0)->unsigned();
            $table->smallInteger('height')->default(0)->unsigned();

            $table->index('image_id');
            $table->index(\DB::raw('query(8)'));
        };
    }
}
