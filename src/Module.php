<?php

namespace siripravi\gallery;

/**
 * gallery module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'siripravi\gallery\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->registerTranslations();
        $this->setComponents([
            'gallery' => [
                'class' => 'siripravi\gallery\components\ImgManager',
                'fkName' => 'fk_id',
                'imgTable' => '{{%image}}',
                'thumbVer'  => 'small',
                'imagePath' => '@webroot/files/images/',
                'versions' => [
                    'small' => ['width' => 72, 'height' => 72],
                    'medium' => ['width' => 200, 'height' => 150],
                    'large' => ['width' => 1920, 'height' => 566],
                ],
            ]
        ]);
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('@siripravi/gallery/' . $category, $message, $params, $language);
    }

    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['@siripravi/gallery/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en',
            'basePath' => '@siripravi/gallery/messages',
            'fileMap' => [
                'modules/gallery/app' => 'app.php',
                'modules/gallery/error' => 'error.php',
            ],
        ];
    }
}
