<?php namespace std\images;

class Support
{
    public static function parseQuery($query)
    {
        $output = [
            'width'          => null,
            'height'         => null,
            'resize_mode'    => 'fill',
            'prevent_upsize' => false
        ];

        if (empty($query)) {
            return $output;
        }

        $query = preg_replace('/\s{2,}/', ' ', $query);
        $parts = explode(' ', $query);

        if (is_numeric($parts[0])) {
            $output['width'] = $parts[0];
        }

        if (is_numeric($parts[1])) {
            $output['height'] = $parts[1];
        }

        if (in_array('fill', $parts)) {
            $output['resize_mode'] = 'fill';
        }

        if (in_array('fit', $parts)) {
            $output['resize_mode'] = 'fit';
        }

        if (in_array('preventUpsize', $parts)) {
            $output['prevent_upsize'] = true;
        }

        return $output;
    }

    public static function normalizeQuery($query = '')
    {
        $queryArray = static::parseQuery($query);

        $normalized = [];

        $normalized[] = is_numeric($queryArray['width']) ? $queryArray['width'] : '-';
        $normalized[] = is_numeric($queryArray['height']) ? $queryArray['height'] : '-';
        $normalized[] = $queryArray['resize_mode'];

        if (!empty($queryArray['prevent_upsize'])) {
            $normalized[] = 'preventUpsize';
        }

        return implode(' ', $normalized);
    }

    public static function getFingerprintPath($md5, $sha1)
    {
        $dirPath = str_split(substr($md5, 0, 8), 2);

        foreach ($dirPath as $n => $segment) { // adBlock fix
            if ($segment == 'ad') {
                $dirPath[$n] = 'ag';
            }
        }

        $fileName = substr($sha1, 0, 8);

        return [implode('/', $dirPath), $fileName];
    }
}
