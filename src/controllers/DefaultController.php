<?php

namespace siripravi\gallery\controllers;

use yii\web\Controller;
use yii\helpers\Html;
use siripravi\gallery\models\Image;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;

/**
 * Default controller for the `image` module
 */
class DefaultController extends Controller
{

    /**
     * Renders the index view for the module
     * @return string
     */

    public $images;

    /**
     * Creates and renders a new version of a specific image.
     * @param integer $id the image id.
     * @param string $version the name of the image version.
     * @throws CHttpException if the requested version is not defined.
     */
    public function actionCreate($id, $version)
    {
        $versions = \Yii::$app->gallery->versions;
        if (isset($versions[$version])) {
            $thumb = \Yii::$app->gallery->createVersion($id, $version);
            $this->getImage($thumb);
        } else
            throw new \yii\web\HttpException(404, Img::t('error', 'Failed to create image! Version is unknown.'));
    }

    public function getImage($imagePath)
    {

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $contentType = finfo_file($fileInfo, $imagePath);
        finfo_close($fileInfo);

        $fp = fopen($imagePath, 'r');

        header("Content-Type: " . $contentType);
        header("Content-Length: " . filesize($imagePath));

        ob_end_clean();
        fpassthru($fp);
    }

    public function actionRemoveImage()
    {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        //$arr = CJSON::decode($str);
        //$id =  $arr['id'];//Yii::app()->end();
        if (\Yii::$app->gallery->delete($id))
            echo 'removed';
        else {
            echo 'not removed';
            die;
        }
    }
    public function actionUploadPhoto()
    {
        $imid = $_GET['fk'];
        $img = UploadedFile::getInstanceByName('file');
        $savedImage = \Yii::$app->gallery->save($img, $imid, '', '');
        \Yii::debug('here');
        !empty($savedImage) ? $mid = $savedImage->id : '';
        $mid = $savedImage->id;
        $this->images[] = $imid;
        $session =  \Yii::$app->session;
        $session['images'] = $this->images;
        if (!empty($mid))
            echo $mid;
        else
            echo 'removed';
    }
}
