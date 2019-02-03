<?php namespace std\images\ui\controllers\main;

class TopBar extends \Controller
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

        $v->assign([
                       'UPLOAD_BUTTON'    => $this->c('\std\ui qqfileuploader:view', [
                           'path'    => '>xhr:upload|',
                           'class'   => 'upload_button',
                           'content' => '<div class="icon fa fa-upload"></div>'//<div class="label">Загрузить</div>'
                       ]),
                       'CAMERA_BUTTON'    => $this->c('\std\ui button:view', [
                           'path'  => '>xhr:camera|',
                           'class' => 'camera_button',
                           'icon'  => 'icon fa fa-video-camera',
                           //                           'label' => 'Камера'
                       ]),
                       'CLICK_MODE_CLASS' => $this->s('~:click_mode')
                   ]);

        $this->css(':\css\std~');

        $this->widget(':|', [
            '.payload' => [
                'model'          => xpack_model($this->imageable),
                'model_instance' => $this->imageableInstance
            ],
            '.r'       => [
                'clickModeToggle' => $this->_abs('>xhr:clickModeToggle|'),
                'loadFromUrl'     => $this->_abs('>xhr:loadFromUrl|')
            ]
        ]);

        return $v;
    }
}
