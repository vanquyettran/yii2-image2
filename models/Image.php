<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/31/2017
 * Time: 12:30 AM
 */

namespace common\modules\image\models;

use Yii;
use yii\validators\FileValidator;
use yii\web\UploadedFile;
use yii\imagine\Image as ImagineImage;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\ImageInterface;
use common\helpers\MyInflector as Inflector;
use common\helpers\MyFileHelper as FileHelper;

class Image extends BaseImage
{
    public $image_file;
    public $image_crop;
    public $image_name_to_basename;
    public $input_resize_keys;

    public $image_source;
    public $image_source_content; // Save image source content after validate
    public $image_source_extension; // Save image source extension after validate
    public $image_source_mime_type; // Save image source mime type after validate
    public $image_source_basename; // Save image source basename after validate
    public $image_source_size; // Save image source size after validate
    public $image_source_loaded; // Check image source was loaded

    /**
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['image_source', 'string'],
            ['image_source', 'url'],
            [['image_source'], 'imageSource', 'skipOnEmpty' => true,
                'params' => [
                'mimeTypes' => Image::getValidImageMimeTypes(),
                'extensions' => Image::getValidImageExtensions(),
                'maxSize' => Image::getMaxImageSize(),
            ]],
            [['image_file'], 'file', 'skipOnEmpty' => true,
                'mimeTypes' => Image::getValidImageMimeTypes(),
                'extensions' => Image::getValidImageExtensions(),
                'maxSize' => Image::getMaxImageSize(),
                'maxFiles' => 1,
            ],
            [['image_crop', 'image_name_to_basename'], 'boolean'],
            [['image_crop', 'image_name_to_basename'], 'default', 'value' => false],
            [['input_resize_keys'], 'each', 'rule' => ['string']],
        ]);
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function imageSource($attribute, $params)
    {
        if ($this->image_source_loaded === true) {
            return true;
        }

        if ($this->image_source_loaded === false) {
            return false;
        }

        try {
            if (!$this->image_source_content = file_get_contents($this->$attribute)) {
                $this->addError($attribute, Yii::t('app', $this->getAttributeLabel($attribute)
                    . ' cannot get content.'));
                $this->image_source_loaded = false;
                return false;
            }
        } catch (\Exception $e) {
            $this->addError($attribute, $e->getMessage());
            $this->image_source_loaded = false;
            return false;
        }

        // Basename
        $parse_url = parse_url($this->image_source);
        $path_info = pathinfo($parse_url['path']);
        if (isset($path_info['basename'], $path_info['extension']) && $path_info['basename'] && $path_info['extension']) {
            $this->image_source_basename = str_replace('.' . $path_info['extension'], '', $path_info['basename']);
        } else {
            $this->image_source_basename = md5(uniqid());
        }

        // Mime type and extension
        $f = finfo_open();
        $this->image_source_mime_type = finfo_buffer($f, $this->image_source_content, FILEINFO_MIME_TYPE);
        finfo_close($f);

        switch ($this->image_source_mime_type) {
            case 'image/jpeg':
                $this->image_source_extension = 'jpg';
                break;
            case 'image/png':
                $this->image_source_extension = 'png';
                break;
            case 'image/gif':
                $this->image_source_extension = 'gif';
                break;
            default:
                if (isset($path_info['extension'])) {
                    $this->image_source_extension = $path_info['extension'];
                }
        }

        if (isset($params['mimeTypes']) && !in_array($this->image_source_mime_type, $params['mimeTypes'])) {
            $this->addError($attribute, Yii::t('app', $this->getAttributeLabel($attribute)
                . ' mime type must be ' . implode(', ', $params['mimeTypes']) . '.'));
            return false;
        }

        if (isset($params['extensions']) && !in_array($this->image_source_extension, $params['extensions'])) {
            $this->addError($attribute, Yii::t('app', $this->getAttributeLabel($attribute)
                . ' extension must be ' . implode(', ', $params['extensions']) . '.'));
            return false;
        }

        // Size
        if (function_exists('mb_strlen')) {
            $this->image_source_size = mb_strlen($this->image_source_content, '8bit');
        } else {
            $this->image_source_size = strlen($this->image_source_content);
        }
        if ($this->image_source_size > $params['maxSize']) {
            $this->addError($attribute, Yii::t('app', $this->getAttributeLabel($attribute)
                . ' size cannot bigger than ' . $params['maxSize'] . ' bytes.'));
            return false;
        }

        // Loaded
        $this->image_source_loaded = true;

        return true;
    }

    /**
     * @return null|UploadedFile
     */
    public function getImageSourceAsUploadedFile()
    {
        if ($this->image_source && $this->validate(['image_source'])) {
            if ($this->image_source_mime_type) {
                $temp_dir = Yii::getAlias('@app/runtime/images');
                if (!file_exists($temp_dir)) {
                    FileHelper::createDirectory($temp_dir);
                }
                $temp_name = "$temp_dir/$this->image_source_basename.$this->image_source_extension";
                if (file_put_contents($temp_name, $this->image_source_content)) {
                    $file = new UploadedFile();
                    $file->name = "$this->image_source_basename.$this->image_source_extension";
                    $file->type = $this->image_source_mime_type;
                    $file->size = $this->image_source_size;
                    $file->error = UPLOAD_ERR_OK;
                    $file->tempName = $temp_name;
                    return $file;
                } else {
                    $this->addError('image_source', Yii::t('app', "Cannot save temp image: $temp_name."));
                }
            } else {
                $this->addError('image_source', Yii::t('app', 'Invalid mime type.'));
            }
        }

        return null;
    }

    /**
     * @param UploadedFile|null $file
     * @return bool
     */
    public function saveFileAndModel(UploadedFile $file = null)
    {
        if ($this->validate(['image_file', 'image_source'])) {
            $this->arrayIfy('input_resize_keys');

            if ($file === null) {
                if (!$file = $this->getImageSourceAsUploadedFile()) {
                    $file = UploadedFile::getInstance($this, 'image_file');
                }
            }

            if ($file) {
                $this->mime_type = $file->type;

                if (!$this->name) {
                    $this->name = $file->basename;
                }

                if ($this->image_name_to_basename) {
                    $this->file_basename = Inflector::slug($this->name);
                } else if (!$this->file_basename) {
                    $this->file_basename = $file->baseName;
                }

                if (!$this->file_extension) {
                    $this->file_extension = $file->extension;
                }

                // @TODO: Save original image
                $this->generatePath();
                $origin_destination = $this->getLocation(Image::ORIGIN_LABEL);
                if (FileHelper::moveImage($file->tempName, $origin_destination, true)) {
                    // @TODO: Save cropped and compressed images
                    $destination = $this->getLocation();
                    $thumb0 = ImagineImage::getImagine()->open($origin_destination);

                    // @TODO: Calculate aspect ratio
                    $size = $thumb0->getSize();
                    $this->width = $size->getWidth();
                    $this->height = $size->getHeight();
                    $this->calculateAspectRatio();

                    if ($this->validate()) {
                        // @TODO: Save compressed image
                        try {
                            $thumb0->save($destination, ['quality' => $this->quality]);
                        } catch (\Exception $e) {
                            $this->addError($this->image_source ? 'image_source' : 'image_file',
                                Yii::t('app', $e->getMessage()));
                        }

                        $resize_labels = [];
                        foreach ($this->input_resize_keys as $input_resize_key) {
                            if ($input_dims = Image::imageDimensionsIfy($input_resize_key)) {
                                if ($this->image_crop) {
                                    $thumb = ImagineImage::getImagine()->open($origin_destination)
                                        ->thumbnail(new Box($input_dims[0], $input_dims[1]), ManipulatorInterface::THUMBNAIL_OUTBOUND)
                                        ->crop(new Point(0, 0), new Box($input_dims[0], $input_dims[1]));
                                } else {
                                    $thumb = ImagineImage::getImagine()->open($origin_destination)
                                        ->thumbnail(new Box($input_dims[0], $input_dims[1]));
                                }
                                // Output
                                $dims = [$thumb->getSize()->getWidth(), $thumb->getSize()->getHeight()];
                                $resize_key = Image::getImageResizeKey($dims);
                                $resize_label = Image::getImageResizeLabel($dims);
                                if ($thumb->save($this->getLocation($resize_label), ['quality' => $this->quality])) {
                                    $resize_labels[$resize_key] = $resize_label;
                                }
                            }
                        }
                        $this->resize_labels = json_encode((object) $resize_labels);

                        if ($this->save()) {
                            return true;
                        }
                    }

                } else {
                    $this->addError($this->image_source ? 'image_source' : 'image_file',
                        Yii::t('app', 'Cannot save image or file is not image.'));
                }
            } else {
                $this->addError($this->image_source ? 'image_source' : 'image_file',
                    Yii::t('app', 'No image uploaded.'));
            }
        }
        return false;
    }

    /**
     * @param UploadedFile|null $file
     * @return bool
     */
    public function updateFileAndModel(UploadedFile $file = null)
    {
        if ($this->validate()) {
            $this->arrayIfy('input_resize_keys');

            if ($file === null) {
                if (!$file = $this->getImageSourceAsUploadedFile()) {
                    $file = UploadedFile::getInstance($this, 'image_file');
                }
            }

            if ($file) {
                $this->mime_type = $file->type;

                if (!$this->file_basename || $this->file_basename == $this->getOldAttribute('file_basename')) {
                    $this->file_basename = $file->baseName;
                }
                if (!$this->file_extension || $this->file_extension == $this->getOldAttribute('file_extension')) {
                    $this->file_extension = $file->extension;
                }
            }

            if (!$this->name) {
                $this->name = $this->file_basename;
            }

            if ($this->image_name_to_basename) {
                $this->file_basename = Inflector::slug($this->name);
            }

            if ($this->validate()) {
                $this->generatePath();
                if ($this->validate(['path'])) {
                    $old_origin_destination = $this->getOldLocation(Image::ORIGIN_LABEL);
                    $origin_destination = $this->getLocation(Image::ORIGIN_LABEL);
                    $destination = $this->getLocation();

                    if (is_file($old_origin_destination)) {
                        if ($this->file_basename != $this->getOldAttribute('file_basename')
                            || $this->file_extension != $this->getOldAttribute('file_extension')
                            || $this->getDirectory() != $this->getOldDirectory()
                        ) {
                            copy($old_origin_destination, $origin_destination);
                            unlink($old_origin_destination);
                        }
                    }

                    if ($file) {
                        $new_image_saved = FileHelper::moveImage($file->tempName,
                            $this->getLocation(Image::ORIGIN_LABEL), true);

                    }

                    if (!isset($new_image_saved) || $new_image_saved) {

                        if (is_file($alias = $this->getOldLocation())) {
                            unlink($alias);
                        }
                        foreach ($this->getOldResizeLabels() as $old_size_label) {
                            if (is_file($alias = $this->getOldLocation($old_size_label))) {
                                unlink($alias);
                            }
                        }

                        if (is_file($origin_destination)) {
                            $thumb0 = ImagineImage::getImagine()->open($origin_destination);

                            // @TODO: Calculate aspect ratio
                            $size = $thumb0->getSize();
                            $this->width = $size->getWidth();
                            $this->height = $size->getHeight();
                            $this->calculateAspectRatio();

                            // @TODO: Save compressed image
                            try {
                                $thumb0->save($destination, ['quality' => $this->quality]);
                            } catch (\Exception $e) {
                                $this->addError($this->image_source ? 'image_source' : 'image_file',
                                    Yii::t('app', $e->getMessage()));
                            }

                            $resize_labels = [];
                            foreach ($this->input_resize_keys as $input_resize_key) {
                                if ($input_dims = Image::imageDimensionsIfy($input_resize_key)) {
                                    if ($this->image_crop) {
                                        $thumb = ImagineImage::getImagine()->open($origin_destination)
                                            ->thumbnail(new Box($input_dims[0], $input_dims[1]), ManipulatorInterface::THUMBNAIL_OUTBOUND)
                                            ->crop(new Point(0, 0), new Box($input_dims[0], $input_dims[1]));
                                    } else {
                                        $thumb = ImagineImage::getImagine()->open($origin_destination)
                                            ->thumbnail(new Box($input_dims[0], $input_dims[1]));
                                    }
                                    // Output
                                    $dims = [$thumb->getSize()->getWidth(), $thumb->getSize()->getHeight()];
                                    $resize_key = Image::getImageResizeKey($dims);
                                    $resize_label = Image::getImageResizeLabel($dims);
                                    if ($thumb->save($this->getLocation($resize_label), ['quality' => $this->quality])) {
                                        $resize_labels[$resize_key] = $resize_label;
                                    }
                                }
                            }
                            $this->resize_labels = json_encode((object) $resize_labels);
                        }

                        $dir = $this->getDirectory();
                        $old_dir = $this->getOldDirectory();
                        if ($dir != $old_dir && FileHelper::isEmptyDirectory($old_dir)) {
                            FileHelper::removeDirectory($old_dir);
                        }

                        if ($this->save()) {
                            return true;
                        }
                    }

                    if (isset($new_image_saved) && !$new_image_saved) {
                        $this->addError($this->image_source ? 'image_source' : 'image_file',
                            Yii::t('app', 'Cannot save image or file is not image.'));
                    }
                }
            }
        }
        return false;
    }

    public function calculateAspectRatio()
    {
        $width = $this->width;
        $height = $this->height;
        // Calculates greatest common divisor
        $find_gcd = function ($a, $b) use (&$find_gcd) {
            return $b ? $find_gcd($b, $a % $b) : $a;
        };
        $gcd = $find_gcd($width, $height);
        $this->aspect_ratio = ($width / $gcd) . ':' . ($height / $gcd);
    }

    public function arrayIfy($attribute)
    {
        $array_str = $this->$attribute;
        if (!is_array($array_str)) {
            if (is_string($array_str)) {
                $this->$attribute = explode(',', $array_str);
            } else {
                $this->$attribute = [];
            }
        }
    }

}