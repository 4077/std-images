<?php namespace std\images\ui\controllers\main\bottomBar;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    private function cacheReset($imageable)
    {
        if ($cacheField = $this->data('cache_field')) {
            $imageable->{$cacheField} = '';
            $imageable->save();
        }
    }

    public function copy()
    {
        if ($model = $this->unxpackModel()) {
            $modelInstance = $this->data('model_instance');

            $sInstance = path(underscore_model($model), $modelInstance);
            $selection = $this->s('~:selection|' . $sInstance);

            $this->s('~:buffer', $selection, RR);

            pusher()->trigger('std/images/buffer_update', [
                'instance' => $this->_instance(),
                'count'    => count($selection)
            ]);
        }
    }

    public function paste()
    {
        if ($target = $this->unxpackModel()) {
            $modelInstance = $this->data('model_instance');

            $buffer = $this->s('~:buffer', []);

            foreach ($buffer as $imageId) {
                if ($sourceImage = \std\images\models\Image::find($imageId)) {
                    $newImage = $target->morphMany(\std\images\models\Image::class, 'imageable')->create($sourceImage->toArray());

                    $sourceImage->versions()->get()->each(function ($version) use ($newImage) {
                        $newImage->versions()->create($version->toArray());
                    });
                }
            }

            $this->data('cache_field', $this->data('cache_field') ?: 'images_cache'); //

            $this->cacheReset($target);

            pusher()->trigger('std/images/update', [
                'instance' => $this->_instance()
            ]);

            $this->c('~:performCallback:update|');
        }
    }

    public function delete()
    {
        if ($this->data('discarded')) {
            $this->c('\std\ui\dialogs~:close:deleteConfirm|std/images');
        } else {
            if ($model = $this->unxpackModel()) {
                $modelInstance = $this->data('model_instance');

                $sInstance = path(underscore_model($model), $modelInstance);
                $selection = &$this->s('~:selection|' . $sInstance);

                $selectionCount = count($selection);

                if ($selectionCount) {
                    if ($this->dataHas('confirmed')) {
                        foreach ($selection as $imageId) {
                            $this->c('^:delete', [
                                'image_id' => $imageId
                            ]);
                        }

                        $buffer = &$this->s('~:buffer', []);

                        $bufferBefore = $buffer;

                        $buffer = array_diff($buffer, $selection);

                        $selection = [];

                        if ($buffer != $bufferBefore) {
                            pusher()->trigger('std/images/buffer_update', [
                                'instance' => $this->_instance(),
                                'count'    => 0
                            ]);
                        }

                        $this->c('\std\ui\dialogs~:close:deleteConfirm|std/images');

                        pusher()->trigger('std/images/update', [
                            'instance' => $this->_instance()
                        ]);

                        $this->c('~:performCallback:update|');
                    } else {
                        $callsData = $this->data;

                        $this->c('\std\ui\dialogs~:open:deleteConfirm|std/images', [
                            'path'          => '\std dialogs/confirm~:view',
                            'data'          => [
                                'confirm_call' => $this->_abs(':delete|', $callsData + ['confirmed' => true]),
                                'discard_call' => $this->_abs(':delete|', $callsData),
                                'message'      => 'Удалить ' . $selectionCount . ' картин' . ending($selectionCount, 'ку', 'ки', 'ок') . '?'
                            ],
                            'pluginOptions' => [
                                'resizable' => false
                            ]
                        ]);
                    }
                }
            }
        }
    }

    public function selectAll()
    {
        if ($model = $this->unxpackModel()) {
            $modelInstance = $this->data('model_instance');

            $sInstance = path(underscore_model($model), $modelInstance);

            $selection = &$this->s('~:selection|' . $sInstance);

            $selection = table_ids($model->morphMany(\std\images\models\Image::class, 'imageable')->get());

            pusher()->trigger('std/images/selection_update', [
                'instance'  => $this->_instance(),
                'selection' => $selection
            ]);
        }
    }

    public function deselect()
    {
        if ($model = $this->unxpackModel()) {
            $modelInstance = $this->data('model_instance');

            $sInstance = path(underscore_model($model), $modelInstance);

            $selection = &$this->s('~:selection|' . $sInstance);

            $selection = [];

            pusher()->trigger('std/images/selection_update', [
                'instance'  => $this->_instance(),
                'selection' => $selection
            ]);
        }
    }
}
