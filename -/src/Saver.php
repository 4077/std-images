<?php namespace std\images;

use Intervention\Image\ImageManager;

class Saver
{
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;

        return call_user_func_array([$instance, $method], $parameters);
    }

    private $tmpDir;

    public function tmpDir($path)
    {
        $this->tmpDir = $path;

        return $this;
    }

    private $outputDir;

    public function outputDir($path)
    {
        $this->outputDir = $path;

        return $this;
    }

    /**
     * @var \Intervention\Image\Image
     */
    private $image;

    private $sourcePath;

    public function sourceFile($path)
    {
        $this->sourcePath = $path;

        $config = dataSets()->get('modules/std-images');

        $envId = app()->getEnv();

        $driver = $config[$envId]['driver'] ?? 'gd';

        $manager = new ImageManager(['driver' => $driver]);

        $this->image = $manager->make($path);

        return $this;
    }

    private $targetModel;

    private $targetModelRelationName;

    public function target($name)
    {
        $this->targetModel = Target::get($name);
        $this->targetModelRelationName = 'images';

        return $this;
    }

    public function targetModel(\Model $model, $imagesRelationName = 'images')
    {
        if (method_exists($model, $imagesRelationName)) {
            $this->targetModel = $model;
            $this->targetModelRelationName = $imagesRelationName;
        } else {
            throw new \Exception('Model ' . get_class($model) . ' has no method ' . $imagesRelationName . '()');
        }

        return $this;
    }

    private $instance = '';

    public function instance($instance)
    {
        $this->instance = $instance ?? '';

        return $this;
    }

    private $query = '';

    public function query($query)
    {
        $this->query = $query;

        return $this;
    }

    private $sourceVersion;

    public function sourceVersion($sourceVersion)
    {
        $this->sourceVersion = $sourceVersion;

        return $this;
    }

    public function saveOrigin($removeSource = false)
    {
        $imageModel = $this->targetModel->{$this->targetModelRelationName}()->create(['instance' => $this->instance]);

        return $this->saveVersion($removeSource, $imageModel);
    }

    public function saveOtherVersion()
    {
        return $this->saveVersion(false, $this->sourceVersion->image);
    }

    private function saveVersion($removeSource, $imageModel)
    {
        if ($this->image) {
            $query = Support::parseQuery($this->query);
            $normalizedQuery = Support::normalizeQuery($this->query);

            if ($query['width'] || $query['height']) {
                $wrapper = new Wrapper;

                $wrapper->sourceSize($this->image->width(), $this->image->height());
                $wrapper->targetSize($query['width'], $query['height']);

                $wrapper->preventUpsize($query['prevent_upsize']);

                $wrapperData = $wrapper->{$query['resize_mode']}();

                $this->image->resize($wrapperData['width'], $wrapperData['height']);
            }

            $tmpFileName = k(16);

            $tmpFileAbsDirPath = $this->tmpDir ? abs_path($this->tmpDir) : app()->c('\std\images~')->_protected();
            $tmpFileAbsPath = $tmpFileAbsDirPath . '/' . $tmpFileName . '.' . $this->image->extension;

            mdir($tmpFileAbsDirPath);

            $this->image->save($tmpFileAbsPath);

            $md5 = md5_file($tmpFileAbsPath);
            $sha1 = sha1_file($tmpFileAbsPath);

            list($dirPath, $fileName) = Support::getFingerprintPath($md5, $sha1);

            $targetDirAbsPath = public_path($this->outputDir, $dirPath);
            $targetFileAbsPath = $targetDirAbsPath . '/' . $fileName . '.' . $this->image->extension;

            if (!file_exists($targetFileAbsPath)) {
                mdir($targetDirAbsPath);
                rename($tmpFileAbsPath, $targetFileAbsPath);
            } else {
                unlink($tmpFileAbsPath);
            }

            $targetFilePath = $this->outputDir . '/' . $dirPath . '/' . $fileName . '.' . $this->image->extension;

            $versionModel = $imageModel->versions()
                ->create([
                             'query'     => $normalizedQuery,
                             'md5'       => $md5,
                             'sha1'      => $sha1,
                             'file_path' => $targetFilePath,
                             'file_size' => filesize($targetFileAbsPath),
                             'width'     => $this->image->width(),
                             'height'    => $this->image->height()
                         ]);

            if ($removeSource) {
                if (file_exists($this->sourcePath)) {
                    unlink($this->sourcePath);
                }
            }

            return $versionModel;
        }
    }
}
