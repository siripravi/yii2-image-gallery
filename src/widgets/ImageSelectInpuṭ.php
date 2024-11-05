<?php

namespace app\widgets;

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\InputWidget;
use siripravi\gallery\assets\ImageWidgetAsset;
use yii\base\ErrorException;

class ImageSelector extends InputWidget {
	/** @var string путь к Responsive File Manager */
	public $fileManagerPathTpl;
	
	public function run() {
		if (!$this->fileManagerPathTpl) {
			throw new ErrorException('Specify fileManagerPathTpl in bootstrap.php for rahulabs\yii2\imgSelector\ImageSelector');
		}
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
		$style= '';
		if(!empty($this->options['value'])) {
            $style = 'background-image:url("' .WEBSITE_URL. $this->options['value'] . '");';
        }

		if(isset($this->model->{$this->attribute})) {
            $style = 'background-image:url("' .WEBSITE_URL.$this->model->{$this->attribute} . '");';
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
	
	private function registerClientScript() {
		
		$view = $this->getView();
		
		static $init = null;
		if (is_null($init)) {
			$init = true;
			$view->registerJs('$( document ).ready(function() { initImageSelectorPopups(); });', View::POS_READY);
		}
		ImageWidgetAsset::register($view);
		
	}
}