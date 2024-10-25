<?php

namespace siripravi\gallery\controllers;

use yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use siripravi\gallery\models\Image;


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
     * @throws yii\web\HttpException if the requested version is not defined.
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

    public function actionRemoveImage($id)
    {
        $id = isset($_POST['id']) ? $_POST['id'] : $id;
        if (\Yii::$app->gallery->delete($id))
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        else {
            echo 'not removed';
            die;
        }
    }
    public function actionUploadPhoto($multiple)
    {
        $img = UploadedFile::getInstanceByName('file');
        $reference = Yii::$app->gallery->getSessionUploadKey();
        $savedImage = new Image();
        if ($multiple) {
            $savedImage = \Yii::$app->gallery->save($img, $reference);
        } else {
            $savedImage = \Yii::$app->gallery->update($img, $reference);
        }
        // if($savedImage instanceof Image)  return $savedImage->id;

        return $savedImage->id;
    }
}
