<?php

use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use yii\helpers\Url;
?>
<style type="text/css">
    input[type=file] {
        cursor: pointer;
    }
</style>

<?php
$count = count($images);
?>
<div class="row gx-5">
    <div id="div_image_select_<?php echo $count ?>" class="image_select">
        <a id="btn_change_image_<?php echo ($count + 1); ?>" class="image-select-edit rel" data-toggle="tooltip" title="click to choose a picture">
            <span><i class="fa fa-solid fa-pen"></i></span>
            <span id="image-select-add-<?php echo ($count + 1); ?>" class=""></span>
            <?php
            $form = ActiveForm::begin([
                'id' => 'frm_img_select' . $count,
                'action' => Url::to(['/gallery/default/upload-photo','multiple' => $multiple]),
                'options' => [
                    'class' => 'form-horizontal',
                    'enctype' => 'multipart/form-data',
                    'data-bs-html' => "true",
                    'data-bs-custom-class' => "custom-tooltip",
                    'data-bs-toggle' => "tooltip",
                    'title' => "Click to change the Logo"
                ],
            ])
            ?>
            <input type="hidden" name="pict" value="<?php echo ($count + 1); ?>" />
            
            <?php ActiveForm::end() ?>
        </a>
    </div>

    <?php Pjax::begin(['id' => 'gallery']) ?>

    <div class="d-grid d-md-flex">
        <p class="lh-lg">Click to Upload Logo!</p>  
        <p>
            
        </p>      
    </div>

    <?= ListView::widget([
       
        'dataProvider' => $dataProvider,
        'options' => [
            'class' => 'row gx-5'
        ],
      
        'pager' => [
            'class' => yii\bootstrap5\LinkPager::class,
           
        ],
        'itemOptions' => [
            'class' => 'col'
        ],

        'layout' => '<div class="row p-3">{items}</div>',
        'itemView' => '_image_item',
        'viewParams' => ['multiple' =>$multiple]
       
    ]); ?>

    <?php Pjax::end() ?>
</div>