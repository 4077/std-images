<?php namespace std\images\ui\controllers\main\topBar;

class Xhr extends \Controller
{
    public $allow = self::XHR;

    private function getModel()
    {
        $this->dmap('~|', 'imageable, target');

        if ($this->data('imageable')) {
            return $this->unpackModel('imageable');
        }

        if ($this->data('target')) {
            return \std\images\Target::get($this->data['target']);
        }
    }

    private function resetCache($imageable)
    {
        $this->dmap('~|', 'cache_field');

        if ($cacheField = $this->data('cache_field')) {
            $imageable->{$cacheField} = '';
            $imageable->save();
        }
    }

    public function upload()
    {
        $this->dmap('~|', 'instance, cache_field');

        if ($imageable = $this->getModel()) {
            $file = $this->c('\std\ui qqfileuploader:receive', [
                'extensions' => 'jpg, jpeg, png, gif'
            ]);

            if ($file->path) {
                $saver = new \std\images\Saver;

                $saver->targetModel($imageable)
                    ->instance($this->data('instance'))
                    ->sourceFile($file->path)
                    ->outputDir('images')
                    ->saveOrigin(true);
            }

            $this->resetCache($imageable);

            $this->c('~:performCallback:update|');
            $this->c('~:reload|');
        }
    }

    public function camera()
    {
        if ($imageable = $this->getModel()) {
            $this->c('\std\ui\camera~:open|std/images/ui/' . $this->_instance(), [
                'callbacks' => [
                    'capture' => $this->_abs('app:capture|', [
                        'imageable' => pack_model($imageable)
                    ])
                ]
            ]);
        }
    }

    public function loadFromUrl()
    {
        $this->dmap('~|', 'instance, cache_field');

        if ($imageable = $this->getModel()) {
            $tmpFileName = $this->_protected(k());

            write($tmpFileName);

            $tmpFile = fopen($tmpFileName, 'w');

            $client = new \GuzzleHttp\Client();

            $response = $client->request('GET', $this->data('url'), [
                'sink' => $tmpFile
            ]);

            $ext = $this->getExtension($response);

            rename($tmpFileName, $tmpFileName . '.' . $ext);

            $saver = new \std\images\Saver;

            $saver->targetModel($imageable)
                ->instance($this->data('instance'))
                ->sourceFile($tmpFileName . '.' . $ext)
                ->outputDir('images')
                ->saveOrigin(true);

            $this->resetCache($imageable);

            $this->c('~:performCallback:update|');
            $this->c('~:reload|');
        }
    }

    public function clickModeToggle()
    {
        $clickMode = &$this->s('~:click_mode');

        if ($clickMode == 'open') {
            $clickMode = 'select';
        } else {
            $clickMode = 'open';
        }

        $this->c('~:reload|');
    }

    function getExtension(\GuzzleHttp\Psr7\Response $response)
    {
        $contentType = explode(';', $response->getHeaderLine('Content-Type'), 2)[0];

        $extensions = [
            'image/png'                => 'png',
            'image/jpg'                => 'jpg',
            'image/jpeg'               => 'jpeg',
            'image/gif'                => 'gif',
            'image/tiff'               => 'tiff',
            'image/bmp'                => 'bmp',
            'application/octet-stream' => false
        ];

        return $extensions[$contentType] ?? null;
    }

    public function delete()
    {
        if ($this->dataHas('image_id numeric')) {
            $this->dmap('~|', 'cache_field');

            $this->c('^:delete', [
                'image_id'    => $this->data['image_id'],
                'cache_field' => $this->data('cache_field')
            ]);

            $this->c('~:performCallback:update|');
            $this->c('~:reload|');
        }
    }

    protected function tokenizeControlData($input, $replacements = [])
    {
        $replacementsKeys = array_keys($replacements);
        $requestDataFlatten = a2f($input);

        foreach ($requestDataFlatten as $path => $value) {
            if (in_array($value, $replacementsKeys, true)) {
                $requestDataFlatten[$path] = $replacements[$value];
            }
        }

        $output = f2a($requestDataFlatten);

        return $output;
    }
}
