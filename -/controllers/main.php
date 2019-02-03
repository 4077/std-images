<?php namespace std\images\controllers;

// todo auto relation
// todo cache_field=cache__std_images

class Main extends \Controller
{
    private $instance;

    public function __create()
    {
        $this->instance = $this->data('instance') or
        $this->instance = '';
    }

    private function getModel()
    {
        if ($this->data('model') instanceof \Model) {
            return $this->data['model'];
        }

        if ($this->data('target')) {
            $this->data('cache_field', 'cache');

            return \std\images\Target::getExists($this->data['target']);
        }
    }

    private function cacheReset($imageable)
    {
        if ($cacheField = $this->data('cache_field')) {
            $imageable->{$cacheField} = '';
            $imageable->save();
        }
    }

    public function delete()
    {
        if ($this->dataHas('image_id numeric')) {
            if ($image = \std\images\models\Image::find($this->data['image_id'])) {
                foreach ($image->versions as $version) {
                    $version->delete();

                    $this->tryUnlink($version);
                }

                $image->delete();

                $this->cacheReset($image->imageable);
            }
        }

        if ($model = $this->getModel()) {
            $relation = $this->data('relation') or
            $relation = 'images';

            $images = $model->{$relation}()->where('instance', $this->instance)->get();

            foreach ($images as $image) {
                foreach ($image->versions as $version) {
                    $version->delete();

                    $this->tryUnlink($version);
                }

                $image->delete();
            }

            $this->cacheReset($model);
        }
    }

    public function tryUnlink($version)
    {
        if ($this->_env('local, remote/crm')) { // todo МЕГАКОСТЫЛЬ
            $hasOtherImages = \std\images\models\Version::where('file_path', $version->file_path)->count();

            if (!$hasOtherImages) {
                @unlink(public_path($version->file_path));

                return true;
            }
        }
    }

    public function arrange()
    {
        $n = 0;
        foreach ($this->data('sequence') as $imageId) {
            $image = \std\images\models\Image::where('id', $imageId)->first();

            if ($image) {
                $image->update(['position' => $n * 10]);

                $n++;
            }
        }

        if (isset($image)) {
            $this->cacheReset($image->imageable);
        }
    }

    public function copy()
    {
        $source = $this->data['source'];
        $target = $this->data['target'];

        $source->images()->orderBy('position')->get()->each(function ($image) use ($target) {
            $newImage = $target->images()->create($image->toArray());

            $image->versions()->get()->each(function ($version) use ($newImage) {
                $newImage->versions()->create($version->toArray());
            });
        });

        $this->data('cache_field', $this->data('cache_field') ?: 'images_cache'); //

        $this->cacheReset($target);
    }

    public function first()
    {
        if ($model = $this->getModel()) {
            $this->css();

            $query = \std\images\Support::normalizeQuery($this->data('query'));

            if ($viewCacheField = $this->data('cache_field')) {
                $cache = _j($model->{$viewCacheField});

                if (empty($cache[$this->instance][$query][0])) {
                    if ($image = $model->images()->where('instance', $this->instance)->where('enabled', true)->orderBy('position')->first()) {
                        if ($version = $this->getVersion($image, $query)) {
                            $cache[$this->instance][$query][0] = [
                                'image'   => $image->toArray(),
                                'version' => $version->toArray(),
                                'view'    => $this->versionView($version)->render()
                            ];

                            $model->{$viewCacheField} = j_($cache);
                            $model->save();
                        }
                    }
                }

                if (!empty($cache[$this->instance][$query][0])) {
                    $image = new \std\images\models\Image();
                    $image->forceFill($cache[$this->instance][$query][0]['image']);

                    $version = new \std\images\models\Version();
                    $version->forceFill($cache[$this->instance][$query][0]['version']);

                    $view = $cache[$this->instance][$query][0]['view'];

                    return new \std\images\Image($image, $version, $view);
                }
            } else {
                if ($image = $model->images()->where('instance', $this->instance)->orderBy('position')->first()) {
                    if ($version = $this->getVersion($image, $query)) {
                        return new \std\images\Image($image, $version, $this->versionView($version));
                    }
                }
            }
        }
    }

    public function get()
    {
        $output = [];

        if ($model = $this->getModel()) {
            $this->css();

            $query = \std\images\Support::normalizeQuery($this->data('query'));

            if ($viewCacheField = $this->data('cache_field')) {
                $cache = _j($model->{$viewCacheField});

                if (empty($cache[$this->instance][$query])) {
                    $images = $model->images()->where('instance', $this->instance)->where('enabled', true)->orderBy('position')->get();

                    foreach ($images as $image) {
                        if ($version = $this->getVersion($image, $query)) {
                            $cache[$this->instance][$query][] = [
                                'image'   => $image->toArray(),
                                'version' => $version->toArray(),
                                'view'    => $this->versionView($version)->render()
                            ];
                        }
                    }

                    $model->{$viewCacheField} = j_($cache);
                    $model->save();
                }

                if (!empty($cache[$this->instance][$query])) {
                    foreach ($cache[$this->instance][$query] as $n => $imageCache) {
                        $image = new \std\images\models\Image();
                        $image->forceFill($cache[$this->instance][$query][$n]['image']);

                        $version = new \std\images\models\Version();
                        $version->forceFill($cache[$this->instance][$query][$n]['version']);

                        $view = $cache[$this->instance][$query][$n]['view'];

                        $output[] = new \std\images\Image($image, $version, $view);
                    }
                }
            } else {
                $images = $model->images()->where('instance', $this->instance)->orderBy('position')->get();

                foreach ($images as $image) {
                    if ($version = $this->getVersion($image, $query)) {
                        $output[] = new \std\images\Image($image, $version, $this->versionView($version));
                    }
                }
            }
        }

        return $output;
    }

    public function getVersion(\std\images\models\Image $image, $query = '')
    {
        $normalizedQuery = \std\images\Support::normalizeQuery($query);

        $version = null;

        if (!$version = $image->versions()->where('query', $normalizedQuery)->first()) {
            $version = $this->createImageFile($image, $normalizedQuery);
        } else {
            if (!file_exists(public_path($version->file_path))) {
                $version->delete();
                $version = $this->createImageFile($image, $normalizedQuery);
            }
        }

        return $version;
    }

    private function createImageFile(\std\images\models\Image $image, $normalizedQuery)
    {
        $normalizedOriginQuery = \std\images\Support::normalizeQuery('');

        if ($origin = $image->versions()->where('query', $normalizedOriginQuery)->first()) {
            $saver = new \std\images\Saver;

            $version = $saver
                ->sourceVersion($origin)
                ->query($normalizedQuery)
                ->outputDir('images')
                ->sourceFile(public_path($origin->file_path))
                ->saveOtherVersion();

            return $version;
        }
    }

    private function versionView(\std\images\models\Version $version)
    {
        $v = $this->v();

        $query = \std\images\Support::parseQuery($version->query);

        $resizeTargetWidth = $query['width'];
        $resizeTargetHeight = $query['height'];
        $resizeMethod = $query['resize_mode'];
        $preventUpsize = $query['prevent_upsize'];

        $wrapData = $this->getWrapData($version, $resizeTargetWidth, $resizeTargetHeight, $resizeMethod, $preventUpsize);

        $img = $this->c('\std\ui tag:view:img', [
            'attrs' => [
                'src' => '/' . $version->file_path,
                'alt' => $this->data('title')
            ]
        ]);

        if ($this->data('href/enabled')) {
            $hrefData = $this->data['href'];

            if (is_scalar($hrefData)) {
                $hrefData = [];
            }

            $query = $hrefData['query'] ?? '';

            if ($otherVersion = $this->getVersion($version->image, $query)) {
                $hrefUrl = '/' . $otherVersion->file_path;
            }

            if (empty($hrefUrl)) {
                $hrefUrl = ap($hrefData, 'url');
            }

            $hrefAttrs = ['href' => $hrefUrl];
            remap($hrefAttrs, $hrefData, 'href, data-fancybox rel, class, title, hover');
            ra($hrefAttrs, ap($hrefData, 'attrs'));

            $href = $this->c('\std\ui tag:view:a', [
                'attrs'   => $hrefAttrs,
                'content' => $img
            ]);

            $content = $href;
        } else {
            $content = $img;
        }

        $v->assign([
                       'WRAPPER_WIDTH'  => $resizeTargetWidth ? $resizeTargetWidth . 'px' : 'auto',
                       'WRAPPER_HEIGHT' => $resizeTargetHeight ? $resizeTargetHeight . 'px' : 'auto',
                       'LEFT'           => $wrapData['left'],
                       'TOP'            => $wrapData['top'],
                       'CONTENT'        => $content
                   ]);

        $this->css();

        return $v;
    }

    private function getWrapData(\std\images\models\Version $version, $resizeTargetWidth = null, $resizeTargetHeight = null, $resizeMethod = null, $preventUpsize = null)
    {
        if (null === $resizeMethod) {
            $resizeMethod = 'fill';
        }

        $wrapper = (new \std\images\Wrapper)->sourceSize($version->width, $version->height);

        $wrapper->targetSize($resizeTargetWidth, $resizeTargetHeight);

        if (null !== $preventUpsize) {
            $wrapper->preventUpsize($preventUpsize);
        }

        return $wrapper->{$resizeMethod}();
    }
}
