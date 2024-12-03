<?php

use yii\helpers\Html;
?>
<style>
    .imgSelectorPreview {
        background: url('') center center no-repeat;
        height: 100px;
        background-size: contain;
        border: 1px dashed #ccc;
    }


</style>
<?php \yii\widgets\Pjax::begin(['id' => 'gallery']) ?>
    <div id="div_image_select_1" class="image_select">
        <a id="btn_change_image_1" class="image-select-edit rel" data-toggle="tooltip" aria-label="click to choose a picture" data-bs-original-title="click to choose a picture">
            <span><i class="fas fa-solid fa-pen"></i></span>
        </a>        
    </div>
    <?= $input; ?>
    <?= Html::img($url);?>
   
    <?php \yii\widgets\Pjax::end() ?>