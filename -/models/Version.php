<?php namespace std\images\models;

class Version extends \Model
{
    protected $table = 'std_images_versions';

    public function image()
    {
        return $this->belongsTo(Image::class);
    }
}
