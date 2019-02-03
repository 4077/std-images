<?php namespace std\images;

class Wrapper
{
    private $sourceWidth;

    private $sourceHeight;

    public function sourceSize($width, $height)
    {
        $this->sourceWidth = $width;
        $this->sourceHeight = $height;

        return $this;
    }

    private $targetWidth;

    private $targetHeight;

    public function targetSize($width = false, $height = false)
    {
        $this->targetWidth = $width;
        $this->targetHeight = $height;

        return $this;
    }

    private $preventUpsize = false;

    public function preventUpsize($value = true)
    {
        $this->preventUpsize = $value;

        return $this;
    }

    private $ratio;

    private $resizeBordersAxis;

    private $left;

    private $top;

    private $width;

    private $height;

    /**
     * растянуть/сжать так, чтобы source полностью поместилось в target
     */
    public function fit()
    {
        $sourceRatio = $this->sourceWidth / $this->sourceHeight;

        if (!$this->targetWidth && !$this->targetHeight) {
            $this->targetWidth = $this->sourceWidth;
            $this->targetHeight = $this->sourceHeight;

            $targetRatio = $sourceRatio;
        } elseif (!$this->targetWidth || !$this->targetHeight) {
            if ($this->targetWidth) {
                $this->targetHeight = $this->targetWidth / $sourceRatio;
            } elseif ($this->targetHeight) {
                $this->targetWidth = $this->targetHeight * $sourceRatio;
            }

            $targetRatio = $sourceRatio;
        } else {
            $targetRatio = $this->targetWidth / $this->targetHeight;
        }

        if ($sourceRatio > $targetRatio) {
            $this->resizeBordersAxis = 'y';
            $this->ratio = $this->targetWidth / $this->sourceWidth;
        } else {
            $this->resizeBordersAxis = 'x';
            $this->ratio = $this->targetHeight / $this->sourceHeight;
        }

        $this->resize();
        $this->center();

        return $this->getOutput();
    }

    /**
     * растянуть/сжать так, чтобы source заняло все пространство target
     */
    public function fill()
    {
        $sourceRatio = $this->sourceWidth / $this->sourceHeight;

        if (!$this->targetWidth && !$this->targetHeight) {
            $this->targetWidth = $this->sourceWidth;
            $this->targetHeight = $this->sourceHeight;

            $targetRatio = $sourceRatio;
        } elseif (!$this->targetWidth || !$this->targetHeight) {
            if ($this->targetWidth) {
                $this->targetHeight = $this->targetWidth / $sourceRatio;
            } elseif ($this->targetHeight) {
                $this->targetWidth = $this->targetHeight * $sourceRatio;
            }

            $targetRatio = $sourceRatio;
        } else {
            $targetRatio = $this->targetWidth / $this->targetHeight;
        }

        if ($sourceRatio > $targetRatio) {
            $this->resizeBordersAxis = 'x';
            $this->ratio = $this->targetHeight / $this->sourceHeight;
        } else {
            $this->resizeBordersAxis = 'y';
            $this->ratio = $this->targetWidth / $this->sourceWidth;
        }

        $this->resize();
        $this->center();

        return $this->getOutput();
    }

    private function resize()
    {
        if ($this->preventUpsize && $this->ratio > 1) {
            $this->ratio = 1;
        }

        $this->width = $this->sourceWidth * $this->ratio;
        $this->height = $this->sourceHeight * $this->ratio;
    }

    public function center()
    {
        $this->left = round(($this->targetWidth - $this->width) / 2);
        $this->top = round(($this->targetHeight - $this->height) / 2);
    }

    private function getOutput()
    {
        return [
            'left'                => $this->left,
            'top'                 => $this->top,
            'width'               => $this->width,
            'height'              => $this->height,
            'resize_borders_axis' => $this->resizeBordersAxis,
            'ratio'               => $this->ratio
        ];
    }
}