<?php namespace std\images\ui\controllers\main\images;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    public function reload()
    {
        $this->c('~:reload|');
    }

    public function edit()
    {
        if ($image = \std\images\models\Image::find($this->data('image_id'))) {
            $this->c('\std\ui\dialogs~:open:editor, ss|std/images', [
                'path'    => '^editor~:view',
                'data'    => [
                    'image'       => pack_model($image),
                    'ui_instance' => $this->_instance()
                ],
                'default' => [
                    'pluginOptions' => [
                        'width'  => 500,
                        'height' => 400
                    ]
                ]
            ]);
        }
    }

    public function toggleSelection()
    {
        if ($image = \std\images\models\Image::find($this->data('image_id'))) {
            $sInstance = path(underscore_model($image->imageable), $image->instance);

            $selection = &$this->s('~:selection|' . $sInstance);

            toggle($selection, $image->id);

            pusher()->trigger('std/images/selection_update', [
                'instance'  => $this->_instance(),
                'selection' => $selection
            ]);
        }
    }

    public function arrange()
    {
        if ($this->dataHas('sequence')) {
            $this->dmap('~|', 'cache_field');

            $this->c('^:arrange', [
                'sequence'    => $this->data['sequence'],
                'cache_field' => $this->data('cache_field')
            ]);

            $this->c('~:performCallback:update|');
            $this->c('~:reload|');
        }
    }
}
