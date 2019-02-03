<?php namespace std\images\ui\controllers;

class App extends \Controller
{
    public function capture()
    {
        if ($imageable = $this->unpackModel('imageable')) {
            $base64 = $this->data('base64');

            $tmpFileName = $this->_protected(k());

            write($tmpFileName);

            $tmpFile = fopen($tmpFileName, 'w');

            fwrite($tmpFile, base64_decode($base64));
            fclose($tmpFile);

            $ext = 'jpeg';

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

    private function resetCache($imageable)
    {
        $this->dmap('~|', 'cache_field');

        if ($cacheField = $this->data('cache_field')) {
            $imageable->{$cacheField} = '';
            $imageable->save();
        }
    }
}
