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
        // '//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css',      
        'css/image-select.css',
        'css/imagewidget.css',
        // 'css/olx.css'
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
