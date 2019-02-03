<?php namespace std\images\editor\controllers;

class Main extends \Controller
{
    private $image;

    private $d;

    private $s;

    public function __create()
    {
        if ($this->image = $this->unpackModel('image')) {
            $this->instance_($this->image->id);

            $this->d = &$this->d('|', [
                'dirty'           => false,
                'resize_mode'     => 'fill',
                'offset'          => [0, 0],
                'offset_rendered' => false,
                'scale'           => 1
            ]);

            $this->s = &$this->s(false, [
                'container_size' => [270, 200],
                'grid'           => [
                    'enabled' => false,
                    'divider' => 2
                ],
                'border'         => [
                    'enabled' => false
                ],
                'force_jpeg'     => false
            ]);

            $this->dmap('|', 'ui_instance');
        } else {
            $this->c('\std\ui\dialogs~:close:editor|std/images');

            $this->lock(false);
        }
    }

    public function reload()
    {
        $this->jquery('|')->replace($this->view());
    }

    public function view()
    {
        $v = $this->v('|');

        $d = &$this->d;
        $s = &$this->s;

        $image = $this->image;

        /**
         * @var $cMain \std\images\controllers\Main
         */
        $cMain = $this->c('^');

        $version = $cMain->getVersion($image);

        $containerWidth = $s['container_size'][0];
        $containerHeight = $s['container_size'][1];

        $wrapper = new \std\images\Wrapper;

        $wrapper->sourceSize($version->width, $version->height);
        $wrapper->targetSize($containerWidth, $containerHeight);

        $wrapData = $wrapper->{$d['resize_mode']}();

        $imageBaseWidth = $wrapData['width'];
        $imageBaseHeight = $wrapData['height'];
        $imageBaseLeft = $wrapData['left'];
        $imageBaseTop = $wrapData['top'];

        $v->assign([
                       'CONTAINER_WIDTH'                 => $containerWidth,
                       'CONTAINER_HEIGHT'                => $containerHeight,
                       'WIDTH'                           => $imageBaseWidth,
                       'HEIGHT'                          => $imageBaseHeight,
                       'LEFT'                            => $imageBaseLeft,
                       'TOP'                             => $imageBaseTop,
                       'SRC'                             => abs_url($version->file_path),
                       'SAVE_BUTTON_DISABLED_CLASS'      => $d['dirty'] ? '' : 'disabled',
                       'FORCE_JPEG_BUTTON_PRESSED_CLASS' => $s['force_jpeg'] ? 'pressed' : '',
                       'FIT_BUTTON'                      => $this->c('\std\ui button:view', [
                           'path'  => '>xhr:fit',
                           'data'  => [
                               'image'       => xpack_model($image),
                               'ui_instance' => $this->data('ui_instance')
                           ],
                           'class' => 'button fit',
                           'icon'  => 'fa fa-compress',
                           'title' => 'Вписать'
                       ]),
                       'FILL_BUTTON'                     => $this->c('\std\ui button:view', [
                           'path'  => '>xhr:fill',
                           'data'  => [
                               'image'       => xpack_model($image),
                               'ui_instance' => $this->data('ui_instance')
                           ],
                           'class' => 'button fill',
                           'icon'  => 'fa fa-expand',
                           'title' => 'Заполнить'
                       ]),
                       'CENTER_BUTTON'                   => $this->c('\std\ui button:view', [
                           'class' => 'button center',
                           'icon'  => 'fa fa-dot-circle-o',
                           'title' => 'Центрировать'
                       ]),
                       'RESET_BUTTON'                    => $this->c('\std\ui button:view', [
                           'class' => 'button reset ' . ($d['dirty'] ? '' : 'hidden'),
                           'icon'  => 'fa fa-refresh',
                           'title' => 'Сбросить изменения'
                       ]),
                   ]);

        $this->css(':\css\std~');

        $this->c('\js\jquery\mousewheel~:load');

        if (!$d['offset_rendered']) {
            ra($d, [
                'offset'          => [$imageBaseLeft, $imageBaseTop],
                'offset_rendered' => true
            ]);
        }

        $this->widget(':|', [
            'containerSize' => $s['container_size'],
            'base'          => [
                'size'  => [$imageBaseWidth, $imageBaseHeight],
                'ratio' => $wrapData['ratio']
            ],
            'dirty'         => $d['dirty'],
            'offset'        => $d['offset'],
            'scale'         => $d['scale'],
            'grid'          => $s['grid'],
            'border'        => $s['border'],
            '.payload'      => [
                'image'       => xpack_model($image),
                'ui_instance' => $this->data('ui_instance')
            ],
            '.r'            => [
                'reset'           => $this->_p('>xhr:reset'),
                'dirty'           => $this->_p('>xhr:dirty'),
                'updateGrid'      => $this->_p('>xhr:updateGrid'),
                'updateBorder'    => $this->_p('>xhr:updateBorder'),
                'updateForceJpeg' => $this->_p('>xhr:updateForceJpeg'),
                'apply'           => $this->_p('>xhr:apply'),
                'update'          => $this->_p('>xhr:update'),
                'updateDimension' => $this->_p('>xhr:updateDimension')
            ]
        ]);

        return $v;
    }
}
