<?php
namespace siripravi\gallery\assets;
use yii\web\AssetBundle;

class ImageWidgetAsset extends AssetBundle
{
    public $js = [
        'js/imagewidget.js',
        'js/jquery.form.js',
        'js/jquery-custom-file-input.js'
    ];

    public $css = [
        'css/image-select.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];
    public function init()
    {
        $this->sourcePath = __DIR__;
        parent::init();
    }
}