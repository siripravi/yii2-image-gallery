<?php

namespace siripravi\gallery\widgets;

use yii;
use siripravi\gallery\assets\ImageWidgetAsset;
use siripravi\gallery\models\Image;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class ImageWidget extends Widget
{
    public $key;
    public $imageMaxCount = 10;
    private $imageData;
    public $uploadUrl;

    public function init()
    {
        parent::init();
        $this->imageData = array($this->imageMaxCount);
        $this->uploadUrl = Url::to(['gallery/default/upload-photo', 'fk' => $this->key]);
    }
    public function getImages()
    {
        $fkName = Yii::$app->gallery->fkName;  // 'fk_id'
        $imgTable = Yii::$app->gallery->imgTable;
        $sql = "SELECT
        id, " . $fkName . ", filename                       
        FROM " . $imgTable . "
        where " . $fkName . " = " . $this->key;
        $images = Image::findBySql($sql)->all();
        $data = ArrayHelper::toArray($images, [
            'siripravi\gallery\models\Image' => [
                'id',
                $fkName,
                'filename',
                'createTime' => 'created',
                'imageSrc' => function ($image) {
                    $thumbVer = Yii::$app->gallery->thumbVer;
                    return Url::to(['gallery/default/create', 'id' => $image->id, 'version' => $thumbVer]);
                },
            ],
        ]);

        return $data;
    }

    public function run()
    {
        ImageWidgetAsset::register($this->getView());
        $this->imageData = $this->getImages($this->key);
        return $this->render('imagewidget', ['images' => $this->imageData, 'uploadUrl' => $this->uploadUrl]);
    }
    /* public function getViewPath()
    {
        return '@app/modules/gallery/views/';
    }*/
}
