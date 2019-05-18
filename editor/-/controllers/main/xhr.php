<?php namespace std\images\editor\controllers\main;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    private function cacheReset($imageable)
    {
        $imageable->images_cache = '';
        $imageable->save();
    }

    public function reset()
    {
        if ($image = $this->unxpackModel('image')) {
            $d = &$this->d('~|' . $image->id);

            ra($d, [
                'dirty'           => false,
                'resize_mode'     => 'fill',
                'offset'          => [0, 0],
                'offset_rendered' => false,
                'scale'           => 1
            ]);

            $this->c('<:reload', [
                'image'       => $image,
                'ui_instance' => $this->data('ui_instance')
            ]);

            pusher()->trigger('std/images/update/dirty', [
                'imageId'  => $image->id,
                'dirty'    => false,
                'instance' => $this->data('ui_instance')
            ]);
        }
    }

    public function dirty()
    {
        if ($image = $this->unxpackModel('image')) {
            $this->d('~:dirty|' . $image->id, $this->data('dirty'), RR);

            pusher()->trigger('std/images/update/dirty', [
                'imageId'  => $image->id,
                'dirty'    => $this->data('dirty'),
                'instance' => $this->data('ui_instance')
            ]);
        }
    }

    public function updateGrid()
    {
        $this->s('~:grid', map($this->data, 'enabled, divider'), RA);
    }

    public function updateBorder()
    {
        $this->s('~:border', map($this->data, 'enabled'), RA);
    }

    public function updateForceJpeg()
    {
        $this->s('~:force_jpeg', map($this->data, 'force_jpeg'), RA);
    }

    public function update()
    {
        if ($image = $this->unxpackModel('image')) {
            $d = &$this->d('~|' . $image->id);

            if ($offset = $this->data('offset')) {
                $d['dirty'] = true;
                $d['offset'] = $offset;

                pusher()->trigger('std/images/update/dirty', [
                    'imageId'  => $image->id,
                    'dirty'    => true,
                    'instance' => $this->data('ui_instance')
                ]);
            }

            if ($scale = $this->data('scale')) {
                $d['dirty'] = true;
                $d['scale'] = $scale;

                pusher()->trigger('std/images/update/dirty', [
                    'imageId'  => $image->id,
                    'dirty'    => true,
                    'instance' => $this->data('ui_instance')
                ]);
            }
        }
    }

    public function fill()
    {
        if ($image = $this->unxpackModel('image')) {
            $d = &$this->d('~|' . $image->id);
            $s = &$this->s('~');

            $d['dirty'] = true;
            $d['offset_rendered'] = false;
            $d['resize_mode'] = 'fill';
            $d['scale'] = 1;

            pusher()->trigger('std/images/update/dirty', [
                'imageId'  => $image->id,
                'dirty'    => true,
                'instance' => $this->data('ui_instance')
            ]);

            $this->c('<:reload', [
                'image'       => $image,
                'ui_instance' => $this->data('ui_instance')
            ]);
        }
    }

    public function fit()
    {
        if ($image = $this->unxpackModel('image')) {
            $d = &$this->d('~|' . $image->id);
            $s = &$this->s('~');

            $d['dirty'] = true;
            $d['offset_rendered'] = false;
            $d['resize_mode'] = 'fit';
            $d['scale'] = 1;

            pusher()->trigger('std/images/update/dirty', [
                'imageId'  => $image->id,
                'dirty'    => true,
                'instance' => $this->data('ui_instance')
            ]);

            $this->c('<:reload', [
                'image'       => $image,
                'ui_instance' => $this->data('ui_instance')
            ]);
        }
    }

    public function updateDimension()
    {
        if ($image = $this->unxpackModel('image')) {
            $dimension = $this->data('dimension');
            $value = $this->data('value');

            if (($dimension == 0 || $dimension == 1) && $value == (int)$value && $value > 0 && $value < 4096) {
                $s = &$this->s('~');

                $s['container_size'][$dimension] = $value;

                $this->c('<:reload', [
                    'image'       => $image,
                    'ui_instance' => $this->data('ui_instance')
                ]);
            }
        }
    }

    public function apply()
    {
        if ($imageModel = $this->unxpackModel('image')) {
            $d = &$this->d('~|' . $imageModel->id);
            $s = $this->s('~');

            $ratio = $this->data('ratio');
            $scale = $d['scale'];

            $containerWidth = $s['container_size'][0];
            $containerHeight = $s['container_size'][1];

            $canvasWidth = round($containerWidth / $ratio / $scale);
            $canvasHeight = round($containerHeight / $ratio / $scale);

            $offsetLeft = round($d['offset'][0] / $ratio / $scale);
            $offsetTop = round($d['offset'][1] / $ratio / $scale);

            /**
             * @var $cMain \std\images\controllers\Main
             */
            $cMain = $this->c('^');

            $version = $cMain->getVersion($imageModel);

            //

            $config = dataSets()->get('modules/std-images');

            $envId = app()->getEnv();

            $driver = $config[$envId]['driver'] ?? 'gd';

            $manager = new \Intervention\Image\ImageManager(['driver' => $driver]);

            $image = $manager->make($version->file_path);

            //

            $canvas = $manager->canvas($canvasWidth, $canvasHeight, '#ffffff');

            $canvas->insert($image, 'top-left', $offsetLeft, $offsetTop);

            $tmpFileName = k(8);

            $tmpFileAbsDirPath = app()->c('\std\images~')->_protected();

            $extension = $s['force_jpeg'] ? 'jpeg' : $image->extension;

            $tmpFileAbsPath = $tmpFileAbsDirPath . '/' . $tmpFileName . '.' . $extension;

            $canvas->save($tmpFileAbsPath);

            $saver = new \std\images\Saver;

            $position = $imageModel->position;
            $imageable = $version->image->imageable;

            $newVersion = $saver->targetModel($imageable)
                ->instance($version->image->instance)
                ->sourceFile($tmpFileAbsPath)
                ->outputDir('images')
                ->saveOrigin(true);

            if ($this->data('replace')) {
                $this->c('^:delete', [
                    'image_id' => $version->image_id
                ]);

                $newVersion->image->position = $position;
                $newVersion->image->save();

                $this->c('^:arrange', [
                    'sequence'    => table_ids($imageable->images()->orderBy('position')->get()),
                    'cache_field' => 'images_cache'
                ]);

                $this->c('\std\ui\dialogs~:close:editor|std/images');

                $this->cacheReset($imageable);
            }

            pusher()->trigger('std/images/update', [
                'instance' => $this->data('ui_instance')
            ]);

            $this->c('^ui~:performCallback:update|' . $this->data('ui_instance'));
        }
    }
}
