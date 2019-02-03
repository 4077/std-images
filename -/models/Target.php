<?php namespace std\images\models;

class Target extends \Model
{
    protected $table = 'std_images_targets';

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
