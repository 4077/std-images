<?php namespace std\images\models;

class Image extends \Model
{
    protected $table = 'std_images';

    public function versions()
    {
        return $this->hasMany(Version::class);
    }

    public function imageable()
    {
        return $this->morphTo();
    }
}

class ImageObserver
{
    public function creating($model)
    {
        $position = Image
            ::where('imageable_id', $model->imageable_id)
            ->where('instance', $model->instance)
            ->max('position');

        $model->position = $position + 10;
    }
}

Image::observe(new ImageObserver);
