<?php

namespace siripravi\gallery\widgets;

use yii;
use siripravi\gallery\assets\ImageWidgetAsset;
use siripravi\gallery\models\Image;
use siripravi\gallery\models\ImageSearch;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class ImageWidget extends Widget
{
    public $key;
    public $multiple = false;
    private $imageData;
    public $uploadUrl;

    public function init()
    {
        parent::init();
       
        $this->imageData = array();
       // $this->uploadUrl = Url::to(['/gallery/default/upload-photo']);
    }
    public function getImages()
    {
        $reference = Yii::$app->gallery->getSessionUploadKey();
        $fkName = Yii::$app->gallery->fkName;
        $fkClass = Yii::$app->gallery->fkClass;
        $imgTable = Yii::$app->gallery->imgTable;
        $images = Image::find(['reference' => $reference])->all();
        $data = ArrayHelper::toArray($images, [
            'siripravi\gallery\models\Image' => [
                'id',
                $fkName,
                'reference',
                'filename',
                'createTime' => 'created',
                'imageSrc' => function ($image) {
                    $thumbVer = Yii::$app->gallery->thumbVer;
                    return Url::to(['/gallery/default/create', 'id' => $image->id, 'version' => $thumbVer]);
                },
            ],
        ]);

        return $data;
    }

    public function run()
    {
        $searchModel = new ImageSearch(['all' => Yii::$app->request->get('all')]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        ImageWidgetAsset::register($this->getView());
        $this->imageData = $this->getImages();
        return $this->render('imagewidget', [
            'images' => $this->imageData,
            'multiple' => $this->multiple,
            'uploadUrl' => $this->uploadUrl,
            'dataProvider' => $dataProvider
        ]);
    }
    /* public function getViewPath()
    {
        return '@app/modules/gallery/views/';
    }*/
}
