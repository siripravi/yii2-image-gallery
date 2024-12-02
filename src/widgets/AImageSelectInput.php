<?php
/**
 * @author Purnachandra Rao Valluri <provdigi@gmail.com>
 * @copyright 2024
 * @version $Id$
 */

namespace app\widgets;

use yii\helpers\Html;
use yii\bootstrap5\InputWidget;
use yii\bootstrap5\ActiveForm;
use siripravi\gallery\assets\ImageWidgetAsset;

/**
 * AdminLTE TimePicker widget.
 *
 * @author skoro
 */
class ImageSelectInput extends InputWidget
{
    
   
    public $key;
    public $imageMaxCount = 10;
    private $imageData;
    public $uploadUrl;
    
    /**
     * @var array
     */
    public $containerOptions = [];
    public function init()
    {
        parent::init();
        if($this->imageMaxCount > 1){
            \Yii::$app->gallery->setMultiUpload(true);
        }        
        $this->imageData = array($this->imageMaxCount);
        $this->uploadUrl = Url::to(['/gallery/default/upload-photo', 'count'=>$this->imageMaxCount]);
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->initWidget();
       // $this->registerScript();
        
        $options = $this->options;
        Html::addCssClass($options, 'form-control');
        
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $options);
        } else {
            $input = Html::textInput($this->name, $this->value, $options);
        }
        
        $input .= '<span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>';
        
        $containerOptions = $this->containerOptions;
        $containerOptions['id'] = $this->getId();
        Html::addCssClass($containerOptions, 'input-group bootstrap-timepicker timepicker');
        
        return Html::tag('div', $input, $containerOptions);

        
          
    }

    protected funtionInitWidget(){
        /**
         * Auto-set form enctype for file uploads
         */
        if (isset($this->field) && isset($this->field->form) && !isset($this->field->form->options['enctype'])) {
            $this->field->form->options['enctype'] = 'multipart/form-data';
        }
        /**
         * Auto-set multiple file upload naming convention
         */
        if (ArrayHelper::getValue($this->options, 'multiple') && !ArrayHelper::getValue($this->pluginOptions,
                'uploadUrl')) {
            $hasModel = $this->hasModel();
            if ($hasModel && strpos($this->attribute, '[]') === false) {
                $this->attribute .= '[]';
            } elseif (!$hasModel && strpos($this->name, '[]') === false) {
                $this->name .= '[]';
            }
        }
        $input = $this->getInput('fileInput');
        return $input;
    }
    
    /**
     * Include assets and enable plugin.
     */
    protected function registerScript()
    {
        if ($this->mode === static::MODE_24H) {
            $this->clientOptions['showMeridian'] = false;
        }
        
        if ($this->hasModel() || $this->value) {
            $this->clientOptions['defaultTime'] = false;
        }
        
        TimePickerAsset::register($this->getView());
        
        $this->registerPlugin('timepicker');
    }
}
