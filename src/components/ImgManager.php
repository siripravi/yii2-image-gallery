<?php

namespace siripravi\gallery\components;

use yii;
use yii\base\Component;
use siripravi\gallery\models\Image;
use siripravi\gallery\components\ImgException;
use ParagonIE\ConstantTime\Encoding;
use yii\db\Expression;
use yii\imagine;
use Imagick;
use Imagine\Image\Box;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use yii\imagine\Image as Picture;

/**
 * Image manager class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license New BSD License
 * @since 0.5
 */

/**
 * Provides easy image manipulation with the help of the excellent PHP Thumbnailer library.
 * @see http://phpthumb.gxdlabs.com/
 */
//require_once(dirname(__FILE__).'/../vendors/phpthumb/ThumbLib.inc.php'); // Yii::import() will not work in this case.

class ImgManager extends Component
{
    public $fkName = "fk_id";
    public $imgTable;   //{{%image}}

    public $thumbVer;
    public $imageUploadSessionKey = 'image_select_upload';
    public $multiUpload = false;
    /**
     *
     * @property array
     */
    public $thumbOptions = array();

    /**
     * @property string the relative path where to store images.
     */
    public $imagePath = 'uploads';

    /**
     * @property array the image versions.
     */
    public $versions = array();

    /**
     * @property string the base path.
     */
    protected $_basePath;

    /**
     * @property string the image version path.
     */
    protected $_versionBasePath;
    private static $_thumbOptions = array(); // needed for the static factory-method
    private static $_imagePath;

    /**
     * Initializes the component.
     */
    public function init()
    {
        self::$_thumbOptions = $this->thumbOptions;
        self::$_imagePath = $this->getImagePath(true);

        parent::init();
    }

    /**
     * Returns the URL for a specific image.
     * @param string $id the image id.
     * @param string $version the name of the image version.
     * @param boolean $absolute whether or not to get an absolute URL.
     * @return string the URL.
     * @throws CException if the version is not defined.
     */
    public function getURL($id, $version, $absolute = false)
    {
        if (isset($this->versions[$version])) {
            $image = $this->loadModel($id);
            $path = $this->getVersionPath($version) . $image->getPath() . $this->resolveFileName($image);
            return \Yii::$app->request->getBaseUrl($absolute) . DIRECTORY_SEPARATOR . $path;
        } else
            throw new ImgException(Img::t('error', 'Failed to get image URL! Version is unknown.'));
    }

    /**
     * Saves a new image.
     * @param CUploadedFile $file the uploaded image.
     * @param string $name the image name. Available since 1.2.0
     * @param string $path the path to save the file to. Available since 1.2.1.
     * @return Image the image record.
     * @throws ImageException if saving the image record or file fails.
     */
    public function save($file, $reference, $fkId=null,$fkClass=null,$path = null)
    {

        $trx = \Yii::$app->db->beginTransaction();

        try {
           /* if ($id > 0) {
                $image = Image::findOne($id);
                $this->delete($id);
            }*/

            $image = new Image();  //$this->findImage($reference, $count); //Image::findOne([$fkName => $fk]);


            $image->extension = strtolower($file->extension);
            $image->filename =  md5($file->baseName . time()) . '.' . $file->extension;
            $image->byteSize = $file->size;
            $image->mimeType = $file->type;
            $image->reference = $reference;
            $image->fk_id = $fkId;
            $image->fk_class = $fkClass;
            //  $image->{$this->fkName} = $fk;
            if ($path !== null)
                $image->path = trim($path, DIRECTORY_SEPARATOR);
            if ($image->save() === false) {
            }
            \Yii::debug($image->attributes);

            $path = $this->resolveImagePath($image);

            if (!file_exists($path))
                if (!$this->createDirectory($path))
                    throw new ImgException(Img::t('error', 'Failed to save image! Directory could not be created.'));

            $path .= $this->resolveFileName($image);

            if ($file->saveAs($path) === false)
                throw new ImgException(Img::t('error', 'Failed to save image! File could not be saved.'));

            $trx->commit();
            return $image;
        } catch (CException $e) {
            $trx->rollback();
            throw $e;
        }
    }

    public function update($file, $reference,$fkId=null,$fkClass=null,$path='')
    {
        $trx = \Yii::$app->db->beginTransaction();

        try {
                      
            $image = $this->loadImage($reference,$fkId);
          
            //Remove Image versions existing
            if ($image instanceof Image && ($image->id > 0)) {
                $path1 = $this->resolveImagePath($image) . $this->resolveFileName($image);    
                if (file_exists($path1) !== false && unlink($path1) === false)
                    throw new ImgException(Img::t('error', 'Failed to delete image! File could not be deleted.'));    
                foreach ($this->versions as $version => $config) {
                   
                    $this->deleteVersion($image, $version);
                }
                $path1 = "";
            }

            //Update the current record with new image 
            $image->extension = strtolower($file->extension);
            $image->filename =  md5($file->baseName . time()) . '.' . $file->extension;
            $image->byteSize = $file->size;
            $image->mimeType = $file->type;
            $image->reference = $reference;
            $image->fk_id = $fkId;
            $image->fk_class = $fkClass;
            //  $image->{$this->fkName} = $fk;
            if ($path !== null)
                $image->path = trim($path, DIRECTORY_SEPARATOR);
            if ($image->save() === false) {
            }
          
            $path1 = $this->resolveImagePath($image);

            if (!file_exists($path1))
                if (!$this->createDirectory($path1))
                    throw new ImgException(Img::t('error', 'Failed to save image! Directory could not be created.'));

            $path1 .= $this->resolveFileName($image);

            if ($file->saveAs($path1) === false)
                throw new ImgException(Img::t('error', 'Failed to save image! File could not be saved.'));

            $trx->commit();
            return $image;
        } catch (CException $e) {
            $trx->rollback();
            throw $e;
        }
    }

    /**
     * Creates a new version of a specific image.
     * @param integer $id the image id.
     * @param string $version the image version.
     * @return ThumbBase
     */
    public function createVersion($id, $version)
    {
        if (isset($this->versions[$version])) {
            $image = $this->loadModel($id);
            if ($image != null) {
                $fileName = $this->resolveFileName($image);
                $imagePath = $this->resolveImagePath($image);
                $options = ImgOptions::create($this->versions[$version]);
                $path = $this->resolveImageVersionPath($image, $version);

                if (!file_exists($path))
                    if (!$this->createDirectory($path))
                        throw new ImgException(Img::t('error', 'Failed to create version! Directory could not be created.'));

                $thumbPath = $path . DIRECTORY_SEPARATOR . $fileName;              
                Picture::getImagine()->open($imagePath . DIRECTORY_SEPARATOR . $fileName)->thumbnail(new Box($options->width, $options->height))->save($thumbPath, ['quality' => 90]);
                return $thumbPath;
            } else

                throw new ImgException(Img::t('error', 'Failed to create version! Image could not be found.'));
        } else
            throw new ImgException(Img::t('error', 'Failed to create version! Version is unknown.'));
    }

    /**
     * Deletes a specific image.
     * @param $id the image id.
     * @return boolean whether the image was deleted.
     * @throws ImgException if the image cannot be deleted.
     */
    public function delete($id)
    {
        /** @var Image $image */
        $image = Image::findOne($id);

        if ($image instanceof Image) {
            $path = $this->resolveImagePath($image) . $this->resolveFileName($image);

            if (file_exists($path) !== false && unlink($path) === false)
                throw new ImgException(Img::t('error', 'Failed to delete image! File could not be deleted.'));

            foreach ($this->versions as $version => $config) {              
                $this->deleteVersion($image, $version);
            }
        }
        if ($image->delete())        
                return true;
        return false;    
    }

    /**
     * Deletes a specific image version.
     * @param Image $image the image model.
     * @param string $version the image version.
     * @return boolean whether the image was deleted.
     * @throws ImgException if the image cannot be deleted.
     */
    protected function deleteVersion($image, $version)
    {
        if (isset($this->versions[$version])) {
            $path = $this->resolveImageVersionPath($image, $version) . DIRECTORY_SEPARATOR . $this->resolveFileName($image);

            if (file_exists($path) !== false && unlink($path) === false)
                throw new ImgException(Img::t('error', 'Failed to delete the image version! File could not be deleted.'));
        } else
            throw new ImgException(Img::t('error', 'Failed to delete image version! Version is unknown.'));
    }

    /**
     * Loads a thumb of a specific image.
     * @param integer $id the image id.
     * @return ThumbBase
     */
    public function loadThumb($id)
    {
        $image = $this->loadModel($id);

        if ($image !== null) {
            $fileName = $this->resolveFileName($image);
            $thumb = self::thumbFactory($this->resolveImagePath($image) . $fileName);
            return $thumb;
        } else
            return null;
    }

    /**
     * Loads a specific image model.
     * @param integer $id the image id.
     * @return Image
     */
    public function loadModel($id)
    {
        return Image::findOne($id);
    }

    /**
     * Returns the original image file name.
     * @param Image $image the image model.
     * @return string the file name.
     */
    protected function resolveFileName($image)
    {
        if (!empty($image->filename))
            return $image->filename; // . '-' . $image->id . '.' . $image->extension; // 1.2.0 ->
        else
            return $image->id . '.' . $image->extension; // backwards compatibility
    }

    /**
     * Returns the path to a specific image.
     * @param Image $image the image model.
     * @return string the path.
     */
    protected function resolveImagePath($image)
    {
        return $this->getImagePath(true) . $image->getPath() . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the path to a specific image version.
     * @param Image $image the image model.
     * @param string $version the image version.
     * @return string the path.
     */
    protected function resolveImageVersionPath($image, $version)
    {
        return $this->getVersionPath($version, true) . $image->getPath();
    }

    /**
     * Returns the images path.
     * @param boolean $absolute whether or not the path should be absolute.
     * @return string the path.
     */
    protected function getImagePath($absolute = false)
    {
        $path = '';

        if ($absolute === true)
            $path .= $this->getBasePath();

        return $path . $this->imagePath;
    }

    /**
     * Returns the version specific path.
     * @param string $version the name of the image version.
     * @param boolean $absolute whether or not the path should be absolute.
     * @return string the path.
     */
    protected function getVersionPath($version, $absolute = false)
    {
        $path = $this->getVersionBasePath($absolute) . $version;

        // Might be a new version so we need to create the path if it doesn't exist.
        if (!file_exists($path))
            mkdir($path, null, true);

        return $path;
    }

    /**
     * Returns the image version path.
     * @param boolean $absolute whether or not the path should be absolute.
     * @return string the path.
     */
    protected function getVersionBasePath($absolute = false)
    {
        $path = '';

        if ($absolute === true)
            $path .= $this->getBasePath();

        if ($this->_versionBasePath !== null)
            $path .= $this->_versionBasePath;
        else
            $path .= $this->_versionBasePath = $this->getImagePath() . DIRECTORY_SEPARATOR . 'versions' . DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Returns the base path.
     * @return string the path.
     */
    protected function getBasePath()
    {
        if ($this->_basePath !== null)
            return $this->_basePath;
        else
            return $this->_basePath = realpath(\Yii::$app->basePath) . '\\';
    }

    /**
     * Creates the specified directory.
     * @param string $path the directory path.
     * @param integer $mode the file mode.
     * @param boolean $recursive allows the creation of nested directories.
     * @return boolean whether or not the directory was created.
     * @since 1.2.1
     */
    protected function createDirectory($path, $mode = 0777, $recursive = true)
    {
        return mkdir($path, $mode, $recursive);
    }

    /**
     * Normalizes the given string by replacing special characters. �=>a, �=>e, �=>o, etc.
     * @param string $string the text to normalize.
     * @return string the normalized string.
     * @since 1.2.0
     */
    protected function normalizeString($string)
    {
        $string = str_replace(str_split('/\?%*:|"<>. '), '', $string);
        $string = preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'));
        return $string;
    }

    /**
     * Creates a new image.
     * @param string $filePath the image file path.
     * @return ImgThumb
     */
    protected static function thumbFactory($filePath)
    {
        $phpThumb = PhpThumbFactory::create($filePath, self::$_thumbOptions);
        return new ImgThumb($phpThumb);
    }

    public function loadImage($reference,$fkId=null)
    {
        $qry = ['reference' => $reference];
        if($fkId !== null){
           $qry = ['fk_id'=>$fkId];
        }
        $image = Image::findOne($qry);
        if (empty($image)) {
            $image = new Image;
        }
        return $image;
    }
    public function setMultiUpload($mu)
    {
        $this->multiUpload = $mu ? true : false;
    }
    public function getMultiUpload()
    {
        return $this->multiUpload;
    }

    public function getSessionUploadKey()
    {
        $keyName = $this->imageUploadSessionKey;
        $session =  \Yii::$app->session;
        if (isset($session[$keyName])) {
            $keyValue = $session[$keyName];
        } else {
            $data = random_bytes(16);
            $keyValue = $session[$keyName] = Encoding::hexEncodeUpper($data);
        }
        return $keyValue;
    }
}
