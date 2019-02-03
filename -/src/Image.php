<?php namespace std\images;

class Image
{
    public $imageModel;

    public $versionModel;

    public $view;

    public function __construct(\std\images\models\Image $imageModel, \std\images\models\Version $versionModel, $view)
    {
        $this->imageModel = $imageModel;
        $this->versionModel = $versionModel;
        $this->view = $view;
    }
}
