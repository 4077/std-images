<?php namespace std\images\ui\controllers\main\images;

class Grid extends \Controller
{
    private $imageable;

    private $imageableInstance;

    public function __create()
    {
        $this->dmap('~|', 'target, imageable, instance, cache_field, href, callbacks, dev_info');

        $this->imageable = $this->unpackModel('imageable');

        if ($this->data('imageable')) {
            $this->imageable = $this->unpackModel('imageable');
        }

        if ($this->data('target')) {
            $this->imageable = \std\images\Target::get($this->data['target']);
        }

        $this->imageableInstance = $this->data('instance');
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $sInstance = path(underscore_model($this->imageable), $this->imageableInstance);

        $selection = &$this->s('~:selection|' . $sInstance);

        $clickMode = $this->s('~:click_mode');

        $images = $this->c('^:get', [
            'model'    => $this->imageable,
            'instance' => $this->imageableInstance,
            'query'    => '120 120 fill',
            'href'     => $clickMode == 'open' ? $this->data('href') : false
        ]);

        $imageablePack = pack_model($this->imageable);

        foreach ($images as $image) {
            $dirty = $this->d('@editor~:dirty|' . $image->imageModel->id);

            $v->assign('image', [
                'ID'            => $image->imageModel->id,
                'CONTENT'       => $image->view,
                'CHECKED_CLASS' => in_array($image->imageModel->id, $selection) ? 'checked' : '',
                'DIRTY_CLASS'   => $dirty ? 'dirty' : ''
            ]);
        }

        if ($clickMode == 'open') {
            $this->c('\plugins\fancybox3~:bind', [
                'selector'      => $this->_selector('|'),
                'item_selector' => 'a',
                'rel'           => underscore_model_type($this->imageable)
            ]);
        }

        $this->c('\std\ui sortable:bind', [
            'selector'       => $this->_selector('|') . ' .images',
            'path'           => '@xhr:arrange|',
            'data'           => [
                'model'    => $imageablePack,
                'instance' => $this->imageableInstance,
            ],
            'items_id_attr'  => 'image_id',
            'plugin_options' => [
                'distance' => 25
            ]
        ]);

        $this->css();

        $this->widget(':|', [
            'instance'  => $this->_instance(),
            'clickMode' => $clickMode,
            '.payload'  => [
                'model'          => xpack_model($this->imageable),
                'model_instance' => $this->imageableInstance
            ],
            '.r'        => [
                'edit'            => $this->_p('@xhr:edit|'),
                'reload'          => $this->_p('@xhr:reload|'),
                'toggleSelection' => $this->_p('@xhr:toggleSelection|')
            ]
        ]);

        return $v;
    }
}
