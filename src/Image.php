<?php

namespace danperron\imagelib;

/**
 * Description of Image
 *
 * @author Dan
 */
class Image {

    /**
     *
     * @var resource 
     */
    private $data = null;
    private $width = 0;
    private $height = 0;

    const TYPE_PNG = 'png';
    const TYPE_GIF = 'gif';
    const TYPE_JPEG = 'jpg';
    const TYPE_GD = 'gd';

    private function __construct($data) {
        $this->setImageData($data);
    }

    public function __destruct() {
        \imagedestroy($this->data);
    }

    /**
     * get the image height
     * 
     * @return int 
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * get the image width
     * 
     * @return int 
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * 
     * @param string $filename
     * @return Image
     * @throws ImageException
     */
    public static function load($filename) {
        $matches = array();
        if (preg_match('/\.(.{2,4})$/', $filename, $matches)) {
            $type = strtolower($matches[1]);
        } else {
            throw new ImageException("Invalid extension on $filename", ImageException::ERR_LOAD);
        }

        switch ($type) {
            case self::TYPE_JPEG:
            case 'jpeg';
                return self::loadJPEG($filename);
            case self::TYPE_GIF:
                return self::loadGIF($filename);
            case self::TYPE_PNG:
                return self::loadPNG($filename);
            case self::TYPE_GD:
                return self::loadGD($filename);
            default:
                throw new ImageException("Invalid Format - $type");
        }
    }

    /**
     * load an image from a PNG file
     * 
     * @param type $fileName
     * @return Image
     * @throws ImageException 
     */
    private static function loadPNG($fileName) {
        $data = \imagecreatefrompng($fileName);
        if (!$data) {
            throw new ImageException('Unable to create image from PNG', null, ImageException::ERR_LOAD);
        }
        return new Image($data);
    }

    private static function loadGD($fileName) {
        $data = imagecreatefromgd($filename);

        if (!$data) {
            throw new ImageException('Unable to create image from GD File', null, ImageException::ERR_LOAD);
        }

        return new Image($data);
    }

    /**
     * Load an image from a JPEG file
     * 
     * @param type $fileName
     * @return \Image
     * @throws ImageException 
     */
    private static function loadJPEG($fileName) {


        $imageData = \imagecreatefromjpeg($fileName);

        if (!$imageData) {
            throw new ImageException('Unable to load JPEG', null, ImageException::ERR_LOAD);
        }
        return new Image($imageData);
    }

    /**
     * load an image from a GIF
     * 
     * @param type $fileName
     * @return \Image
     * @throws ImageException 
     */
    private static function loadGIF($fileName) {
        $data = \imagecreatefromgif($fileName);
        if (!$data) {
            throw new ImageException('Unable to load GIF', null, ImageException::ERR_LOAD);
        }
        return new Image($data);
    }

    /**
     * Sets the Image data 
     * 
     * @param resource $imageResource 
     */
    private function setImageData($imageResource) {
        $this->data = $imageResource;
        $this->width = \imagesx($this->data);
        $this->height = \imagesy($this->data);
    }

    /**
     *
     * Crop an image
     * 
     * @param int $x - starting point x
     * @param int $y - starting point y
     * @param int $w - width of crop
     * @param int $h - height of crop
     * @return \Image
     * @throws ImageException 
     */
    public function crop($x, $y, $w, $h) {

        if ($x + $w > $this->getWidth()) {
            throw new ImageException('Crop out of bounds', null, ImageException::ERR_BOUNDS);
        }

        if ($y + $h > $this->getHeight()) {
            throw new ImageException('Crop out of bounds', null, ImageException::ERR_BOUNDS);
        }

        $newData = \imagecreatetruecolor($w, $h);

        $cropped = \imagecopyresampled($newData, $this->data, 0, 0, $x, $y, $w, $h, $w, $h);

        if (!$cropped) {
            throw new ImageException('Unable to crop', null, 0);
        }

        return new Image($newData);
    }

    /**
     * Returns a new copy of this image scaled to the specified width and height
     * 
     * @param int $width
     * @param int $height
     * @return \Image 
     * 
     */
    public function scale($width, $height) {
        $newdata = \imagecreatetruecolor($width, $height);
        \imagecopyresampled($newdata, $this->data, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        return new Image($newdata);
    }

    /**
     * Scale to new width value maintaining image aspect ratio.
     * 
     * @param int $width
     * @return type
     */
    public function scaleWidth($width) {
        $aspectRatio = $this->height / $this->width;
        $height = $width * $aspectRatio;
        return $this->scale($width, $height);
    }

    /**
     * Scale to new height value maintaining image aspect ratio.
     * 
     * @param int $height
     * @return type
     */
    public function scaleHeight($height) {
        $aspectRatio = $this->width / $this->height;
        $width = $height * $aspectRatio;
        return $this->scale($width, $height);
    }

    /**
     * Return a desaturated version of the image
     * 
     * @return \Image 
     */
    public function desaturate() {
        return $this->filter(IMG_FILTER_GRAYSCALE);
    }

    /**
     *
     * emboss the image
     * 
     * @return \Image
     * @throws ImageException 
     */
    public function emboss() {
        return $this->filter(IMG_FILTER_EMBOSS);
    }

    /**
     * return a negative version of the image
     * 
     * @return \Image 
     */
    public function negative() {
        return $this->filter(IMG_FILTER_NEGATE);
    }

    /**
     * returns a new copy of that is cropped square
     * 
     * @return \Image 
     */
    public function squarify() {
        $width = $this->getWidth();
        $height = $this->getHeight();

        $newWidth = 0;
        $sourceX = 0;
        $sourceY = 0;

        //Already square
        if ($width == $height) {
            return $this;
        }

        if ($width > $height) {
            $newWidth = $height;
            $sourceX = ($width / 2) - ($newWidth / 2);
        } else {
            $newWidth = $width;
            $sourceY = ($height / 2) - ($newWidth / 2);
        }

        $data = \imagecreatetruecolor($newWidth, $newWidth);
        \imagecopyresampled($data, $this->data, 0, 0, $sourceX, $sourceY, $newWidth, $newWidth, $newWidth, $newWidth);
        return new Image($data);
    }

    /**
     * 
     * Save the image out to a file.
     * 
     * @param string $fileName
     * @param string $type - File type to save out to (Image::TYPE_PNG, Image::TYPE_JPG, etc.)
     * @param int $quality - Image quality for compressed formats.
     * @throws ImageException
     */
    public function save($fileName, $type = self::TYPE_PNG, $quality = 90) {
        switch ($type) {
            case self::TYPE_PNG:
                $this->savePNG($fileName);
                break;
            case self::TYPE_JPEG:
                $this->saveJPEG($fileName, $quality);
                break;
            case self::TYPE_GIF:
                $this->saveGIF($fileName);
                break;
            default:
                throw new ImageException('Invalid Image format');
        }
    }

    /**
     * mirrors the image
     * 
     * @return \danperron\imagelib\Image
     */
    public function mirror() {
        $newData = \imagecreatetruecolor($this->width, $this->height);
        \imagecopy($newData, $this->data, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
        \imageflip($newData, IMG_FLIP_HORIZONTAL);
        return new Image($newData);
    }

    /**
     * 
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     * @return \danperron\imagelib\Image
     */
    public function colorize($red, $green, $blue, $alpha) {
        return $this->filter(IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
    }

    /**
     * 
     * @return \danperron\imagelib\Image
     */
    public function edgeDetect() {
        return $this->filter(IMG_FILTER_EDGEDETECT);
    }

    /**
     * 
     * @return type
     */
    public function meanRemoval() {
        return $this->filter(IMG_FILTER_MEAN_REMOVAL);
    }

    private function filter($filtertype, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null) {
        $newData = @\imagecreatetruecolor($this->width, $this->height) or $this->throwException(new ImageException('Error filtering image'));
        @\imagecopy($newData, $this->data, 0, 0, 0, 0, $this->getWidth(), $this->getHeight()) or $this->throwException(new ImageException("Error filtering image"));
        @\imagefilter($newData, $filtertype, $arg1, $arg2, $arg3, $arg4) or $this->throwException(new ImageException('Error filtering image.'));
        return new Image($newData);
    }

    /**
     * Save image as a png to the file specified
     * 
     * @param string $fileName
     * @throws ImageException 
     */
    private function savePNG($fileName) {
        $saved = \imagepng($this->data, $fileName, 0);

        if (!$saved) {
            throw new ImageException('Could not save PNG', ImageException::ERR_SAVE);
        }
    }

    /**
     * Save image to JPEG
     * 
     * @param string $fileName
     * @param int $quality - a quality value between 0-100
     * @throws ImageException 
     */
    private function saveJPEG($fileName, $quality = null) {
        $saved = imagejpeg($this->data, $fileName, $quality);

        if (!$saved) {
            throw new ImageException('Could not save JPEG', ImageException::ERR_SAVE);
        }
    }

    /**
     * Saves the image as a GIF file
     * 
     * @param string $fileName
     * @throws ImageException 
     */
    private function saveGIF($fileName) {
        $saved = imagegif($this->data, $fileName);

        if (!$saved) {
            throw new ImageException('Could not save GIF', ImageException::ERR_SAVE);
        }
    }

    private function throwException(\Exception $e) {
        throw $e;
    }

}
