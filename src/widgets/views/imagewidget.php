<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
?>
<style type="text/css">
    input[type=file] {
        cursor: pointer;
    }
</style>
<?php
$count = count($images);
?>
<div class="card bg-primary" style="width:auto">
    <div class="card-header">
    <div id="div_image_select_<?php echo $count ?>" class="col-lg-12 image_select" style="">
        <a id="btn_change_image_<?php echo ($count + 1); ?>" class="image-select-edit rel" data-toggle="tooltip" title="click to choose a picture">
        <span><i class="fa fa-solid fa-pen"></i></span>    
        <span id="image-select-add-<?php echo ($count + 1); ?>" class=""></span>
            <?php
            $form = ActiveForm::begin([
                'id' => 'frm_img_select' . $count,
                'action' => $uploadUrl,
                'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data'],
            ])
            ?>
            <input type="hidden" name="pict" value="<?php echo ($count + 1); ?>" />
            <?php ActiveForm::end() ?>
        </a>
    </div>
    </div>
    <?php Pjax::begin(['id' => 'gallery']) ?>
    <div class="card-body">
        <?php

        $i = 0;
        while ($i < $count) {
            if ($i % 3 == 0)
                echo '<div class="row justify-content-left">';

        ?>

            <div class="col-4"  style="width:240px">
                <div class="tools-edit-image">
                    <a id="<?= $images[$i]['id'];  ?>" title="Remove this picture" class="delete">X
                    </a>
                </div>
                <?= Html::img($images[$i]['imageSrc'], ['alt' => "", 'id' => 'pimg-' . ($i + 1), 'class' => 'card-img-top img-fullsize']); ?>

            </div>

        <?php $i++;
            if ($i % 3 == 0)
                echo '</div>';
        }

        ?>
    </div>
    <?php Pjax::end() ?>
</div>