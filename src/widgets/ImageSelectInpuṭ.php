<?php

namespace app\widgets;

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\InputWidget;
use yii\bootstrap5\ActiveForm;
use siripravi\gallery\assets\ImageWidgetAsset;
use yii\base\ErrorException;

class ImageSelectInput extends InputWidget
{
    /** @var string путь к Responsive File Manager */
    public $fileManagerPathTpl;
    public $clientOptions = ['mode' => 'inline','type' =>'text'];
    public $remoteClientOptions = []; 
    public $url = "/invoice/default/upload-logo";
    public $multiple = false;
    public function init()
    {
        // Manual setting element ID.
        if ($this->id) {
            $this->options['id'] = $this->id;
        }
        parent::init();
    }
    public function run()
    {
        $this->registerPlugin('darkEditable');
        
        if ($this->label) {
            $this->options['label'] = $this->label;
        }
       
        $this->registerClientScript();
        $input = '';
        if ($this->hasModel()) {          
            $input = Html::getAttributeValue($this->model, $this->attribute);
          
          /*  $input .= Html::a('Edit','#',[
                'class' => 'link-primary ps-3 link-underline-opacity-25 link-underline-opacity-100-hover',
                'id' => $this->getId(),
                'data-name' => $this->attribute,
                'data-value' => Html::getAttributeValue($this->model, $this->attribute),
                              
               'data-type'  => 'text',
                'data-url' => $this->url,
                'data-pk' => $this->model->primaryKey,
                'data-title' => $this->title
            ]);
          */
          $input .= '<div id="div_image_select_1" class="image_select">';
          $input .= '<a id="btn_change_image_2" class="image-select-edit rel" data-toggle="tooltip" aria-label="click to choose a picture" data-bs-original-title="click to choose a picture">
            <span><i class="fa fa-solid fa-pen"></i></span>';
            $input .= ActiveForm::begin([
                //  'id' => 'frm_img_select' . $count,
                  'action' => Url::to(['/gallery/default/upload-photo','multiple' => $this->multiple]),
                  'options' => [
                      'class' => 'form-horizontal',
                      'enctype' => 'multipart/form-data',
                      'data-bs-html' => "true",
                      'data-bs-custom-class' => "custom-tooltip",
                      'data-bs-toggle' => "tooltip",
                      'title' => "Click to change the Logo"
                  ],
              ]);
              $input .=' <input type="hidden" name="pict" value="<?php echo ($count + 1); ?>" />';
              $input .= ActiveForm::end();
              $input .='</a></div>';
        }         
        return $input;
    }
 
    public function run1()
    {
        /*if (!$this->fileManagerPathTpl) {
			throw new ErrorException('Specify fileManagerPathTpl in bootstrap.php for rahulabs\yii2\imgSelector\ImageSelector');
		}*/
        // иначе начинаем конфигурироваться
        if (!array_key_exists('class', $this->options)) {
            $this->options['class'] = 'form-control';
        }
        $this->options = array_merge($this->options, ['readonly' => true]);
        $input         = Html::hiddenInput($this->name, $this->value, $this->options);
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $this->options);
        }
        $url          = sprintf($this->fileManagerPathTpl, $this->options['id']);
        $selectImgBtn = Html::a('Choose file', $url, [
            'class' => 'btn iframe-btn btn-dark btn-sm',
            'type'  => 'button',
        ]);
        $removeImgBtn = Html::tag('span', 'Delete file', [
            'class'       => 'btn btn-danger js_RemoveImg btn-sm',
            'type'        => 'button',
            'data-img-id' => $this->options['id'],
        ]);
        $style = '';
        if (!empty($this->options['value'])) {
            $style = 'background-image:url("' . WEBSITE_URL . $this->options['value'] . '");';
        }

        if (isset($this->model->{$this->attribute})) {
            $style = 'background-image:url("' . WEBSITE_URL . $this->model->{$this->attribute} . '");';
        }
        $imgPreview   = Html::tag('div', '&nbsp;', [
            'id'    => 'preview__' . $this->options['id'],
            'class' => 'imgSelectorPreview',
            'style' => $style
        ]);
        echo '
			<div class="row">
				<div class="col-sm-12 mb-3">' . $imgPreview . '</div>
				<div class="col-sm-12 center-block">' . $input . '<br>' . $selectImgBtn . ' ' . $removeImgBtn . '</div>
			</div>
		';

        $this->registerClientScript();
    }

  /*  private function registerClientScript()
    {

        $view = $this->getView();

        static $init = null;
        if (is_null($init)) {
            $init = true;
            $view->registerJs('$( document ).ready(function() { initImageSelectorPopups(); });', View::POS_READY);
        }
        ImageWidgetAsset::register($view);
    }*/
    protected function registerClientScript()
    {
        $clientOptions = array_merge($this->clientOptions, $this->remoteClientOptions());
        $clientOptions = Json::encode($clientOptions);
        $view = $this->getView();
        $id = isset($this->options['id']) ? $this->options['id'] : $this->getId();

        $view->registerJs("new DarkEditable($id,$clientOptions);");
        
        if ($this->clientEvents) {
            $js = [];
            foreach ($this->clientEvents as $event => $callback) {
                if (!$callback instanceof JsExpression) {
                    $callback = new JsExpression($callback);
                }
                $js[] = "jQuery('#$id').on('$event', $callback);";
            }
            if (!empty($js)) {
                $js = implode("\n", $js);
                $view->registerJs($js);
            }
        }
        
        $asset = ImageWidgetAsset::register($view);
       /* if ($this->language) {
            $asset->language = $this->language;
        }*/
    }
}
