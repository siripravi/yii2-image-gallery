<?php
namespace siripravi\gallery\components;

use yii\base\Component;
use siripravi\gallery\models\Image;
use siripravi\gallery\components\ImgException;
use Imagine\Gd;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use yii\db\Expression;

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

    /**
     * PhpThumb options that are passed to the ThumbFactory.
     * Default values are the following:
     *
     * <code>
     * array(
     *     'resizeUp' => false,
     *     'jpegQuality' => 100,
     *     'correctPermissions' => false,
     *     'preserveAlpha' => true,
     *     'alphaMaskColor'	=> array(255, 255, 255),
     *     'preserveTransparency' => true,
     *     'transparencyMaskColor' => array(0, 0, 0),
     * );
     * </code>
     *
     * @property array
     */
    public $thumbOptions = array();

    /**
     * @property string the relative path where to store images.
     */
    public $imagePath = 'files\images\\';

    public $fkName = "fk_id";

    public $imgTable;   //{{%image}}
    public $thumbVer;

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
            return \Yii::$app->request->getBaseUrl($absolute) . '/' . $path;
        } else
            throw new ImgException(Img::t('error', 'Failed to get image URL! Version is unknown.'));
    }

    /**
     * Saves a new image.
     * @param CUploadedFile $file the uploaded image.
     * @param string $fk the image name. Available since 1.2.0
     * @param string $path the path to save the file to. Available since 1.2.1.
     * @return Image the image record.
     * @throws ImageException if saving the image record or file fails.
     */
    public function save($file, $fk = null, $path = null, $id = null)
    {
        $trx = \Yii::$app->db->beginTransaction();

        try {

            if ($id > 0) {
                $image = Image::findOne($id);
                //! delete the previously uploaded file
                $this->delete($id);
            } else
                $image = new \siripravi\gallery\models\Image();

            $image->extension = strtolower($file->extension);
            $image->filename = $file->name;
            $image->{$this->fkName} = $fk;
            $image->byteSize = $file->size;
            $image->mimeType = $file->type;
            $image->created = new Expression('NOW()');

            if ($fk !== null)
                $image->{$this->fkName} = $this->normalizeString($fk);

            if ($path !== null)
                $image->path = trim($path, '\\');

            if ($image->save() === false) {
            }
            \Yii::debug($image->attributes);
            // throw new ImgException(Img::t('error','Failed to save image! Record could not be saved.'));

            $path = $this->resolveImagePath($image);

            if (!file_exists($path))
                if (!$this->createDirectory($path))
                    throw new ImgException(Img::t('error', 'Failed to save image! Directory could not be created.'));

            $path .= $this->resolveFileName($image);

            if ($file->saveAs($path) === false)
                throw new ImgException(Img::t('error', 'Failed to save image! File could not be saved.'));

            $trx->commit();

            //   echo $id;\Yii::$app()->end();
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
                //	$thumb=self::thumbFactory($this->resolveImagePath($image).$fileName);
                $options = ImgOptions::create($this->versions[$version]);
                //echo ;; die;
                //$thumb->applyOptions($options);
                $path = $this->resolveImageVersionPath($image, $version);
                /* print_r($options);
                  [width] => 72
                  [height] => 72
                  [percent] =>
                  [resizeMethod] =>
                  [cropX] =>
                  [cropY] =>
                  [cropWidth] =>
                  [cropHeight] =>
                  [cropMethod] =>
                  [rotateDirection] =>
                  [rotateDegrees] =>

                 */
                //    echo $fileName; die;
                if (!file_exists($path))
                    if (!$this->createDirectory($path))
                        throw new ImgException(Img::t('error', 'Failed to create version! Directory could not be created.'));

                $path .= $fileName;

                \yii\imagine\Image::getImagine()->open($this->_basePath . '/files/images/' . $fileName)->thumbnail(new Box($options->width, $options->height))->save($path, ['quality' => 90]);
                return $path;
            } else
                //return $id;
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

            if ($image->delete() === false)
                throw new ImgException(Img::t('error', 'Failed to delete image! Record could not be deleted.'));

            if (file_exists($path) !== false && unlink($path) === false)
                throw new ImgException(Img::t('error', 'Failed to delete image! File could not be deleted.'));

            foreach ($this->versions as $version => $config)
                $this->deleteVersion($image, $version);
        } else
            throw new ImgException(Img::t('error', 'Failed to delete image! Record could not be found.'));
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
            $path = $this->resolveImageVersionPath($image, $version) . $this->resolveFileName($image);

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
        if (!empty($image->{$this->fkName}))
            return $image->{$this->fkName} . '-' . $image->id . '.' . $image->extension; // 1.2.0 ->
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
        return $this->getImagePath(true) . $image->getPath();
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
        $path = $this->getVersionBasePath($absolute) . $version . '\\';
        //echo $path; die;
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
            $path .= $this->_versionBasePath = $this->getImagePath() . 'versions\\';

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
            return $this->_basePath = realpath(\Yii::$app->basePath . '/../') . '\\';
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
}
