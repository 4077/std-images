<?php namespace std\images\controllers;

class App extends \Controller
{
    public function removeNotOriginalVersions()
    {
        $query = \std\images\Support::normalizeQuery();

        $notOriginalVersions = \std\images\models\Version::where('query', '!=', $query)->get();

        $deleted = 0;
        foreach ($notOriginalVersions as $version) {
            $version->delete();

            if ($this->c('~')->tryUnlink($version)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function removeOrphanedFiles()
    {
        $directory = new \RecursiveDirectoryIterator(public_path('images'));
        $iterator = new \RecursiveIteratorIterator($directory);

        $output = [];
        $rmList = [];

        $basePath = public_path() . '/';

        $n = 0;
        foreach ($iterator as $node) {
            if ($node->isFile()) {
                $path = $this->app->paths->getRelativePath($node->getRealPath(), $basePath);

                $output[] = $path;

                $version = \std\images\models\Version::where('file_path', $path)->first();

                if (!$version) {
                    $rmList[] = $node->getRealPath();
                }
            }

            $n++;
        }

        foreach ($rmList as $rmPath) {
            unlink($rmPath);
        }

        return count($rmList) . '/' . count($output);
    }

    public function removeOrphaned()
    {
        $images = \std\images\models\Image::all();

        $n = 0;

        foreach ($images as $image) {
            if (!$image->imageable) {
                $n++;

                $this->c('~:delete', [
                    'image_id' => $image->id
                ]);
            }
        }

        return $n;
    }

    public function getOrphanedList()
    {
        $images = \std\images\models\Image::all();

        $output = [];

        foreach ($images as $image) {
            if (!$image->imageable) {
                $output[] = $image->imageable_type . ':' . $image->imageable_id;
            }
        }

        return $output;
    }

    public function getUnexistingList()
    {
        $versions = \std\images\models\Version::all();

        $output = [];

        foreach ($versions as $version) {
            $path = public_path($version->file_path);

            if (!file_exists($path)) {
                $output[] = 'version_id=' . $version->id . ': ' . $path;
            }
        }

        return $output;
    }

    public function getVersionsWithoutImagesCount()
    {
        return \std\images\models\Version::doesntHave('image')->count();
    }

    public function getImagesWithoutVersions()
    {
        return \std\images\models\Image::doesntHave('versions')->count();
    }

    public function removeUnexistingVersions()
    {
        $versions = \std\images\models\Version::all();

        $n = 0;
        foreach ($versions as $version) {
            $path = public_path($version->file_path);

            if (!file_exists($path)) {
                $version->delete();
                $n++;
            }
        }

        return $n . ' deleted';
    }

    public function trimLSlash()
    {
        $versions = \std\images\models\Version::all();

        foreach ($versions as $version) {
            $stripped = trim_l_slash($version->file_path);

            if ($stripped != $version->file_path) {
                $version->file_path = $stripped;
                $version->save();
            }
        }
    }

    public function ad2ag()
    {
        $ads = \std\images\models\Version::where('file_path', 'like', '%/ad/%')->orderBy('id')->get();

        $n = 0;
        foreach ($ads as $ad) {
            $sourcePath = $ad->file_path;
            $targetPath = str_replace('/ad/', '/ag/', $sourcePath);

            $sourceAbsPath = public_path($sourcePath);
            $targetAbsPath = public_path($targetPath);

            $targetInfo = new \SplFileInfo($targetAbsPath);

            $targetDir = $targetInfo->getPath();

            mdir($targetDir);

            if (file_exists($sourceAbsPath)) {
                rename($sourceAbsPath, $targetAbsPath);
            }

            $ad->file_path = $targetPath;
            $ad->save();

            $n++;
        }

        return 'renamed ' . $n;
    }

    public function resetImagesCache()
    {
        \std\images\models\Target::query()->update(['cache' => '']);
    }
}
