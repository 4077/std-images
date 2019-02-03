<?php namespace std\images\ui\controllers\main;

class BottomBar extends \Controller
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
        $buffer = $this->s('~:buffer', []);

        $images = $this->imageable->morphMany(\std\images\models\Image::class, 'imageable')->get();

        $v->assign([
                       'COPY_BUTTON_DISABLED_CLASS'       => count($selection) == 0 ? 'disabled' : '',
                       'PASTE_BUTTON_DISABLED_CLASS'      => count($buffer) == 0 ? 'disabled' : '',
                       'BUFFER_COUNT'                     => count($buffer),
                       'DELETE_BUTTON_DISABLED_CLASS'     => count($selection) == 0 ? 'disabled' : '',
                       'SELECT_ALL_BUTTON_DISABLED_CLASS' => count($selection) == count($images) ? 'disabled' : '',
                       'DESELECT_BUTTON_DISABLED_CLASS'   => count($selection) == 0 ? 'disabled' : ''
                   ]);

        $this->css(':\css\std~');

        $this->widget(':|', [
            'instance'      => $this->_instance(),
            'model'         => xpack_model($this->imageable),
            'modelInstance' => $this->imageableInstance,
            '.payload'      => [
                'model'          => xpack_model($this->imageable),
                'model_instance' => $this->imageableInstance
            ],
            '.r'            => [
                'copy'      => $this->_abs('>xhr:copy|'),
                'paste'     => $this->_abs('>xhr:paste|'),
                'delete'    => $this->_abs('>xhr:delete|'),
                'selectAll' => $this->_abs('>xhr:selectAll|'),
                'deselect'  => $this->_abs('>xhr:deselect|')
            ],
            '.w'            => [
                'images' => $this->_w('~images/grid:|')
            ]
        ]);

        return $v;
    }
}
