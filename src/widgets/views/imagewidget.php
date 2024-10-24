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
<div id="div_image_select_<?php echo $count ?>" class="col-lg-12 image_select">
    <a id="btn_change_image_<?php echo ($count + 1); ?>" class="image-select-edit rel" data-toggle="tooltip" title="click to choose a picture">
        <span><i class="fa fa-solid fa-pen"></i></span>
        <span id="image-select-add-<?php echo ($count + 1); ?>" class=""></span>
        <?php
        $form = ActiveForm::begin([
            'id' => 'frm_img_select' . $count,
            'action' => $uploadUrl,
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
<div class="card-body">
    <?php

    $i = 0;
    while ($i < $count) {
        if (($count > 1) && ($i % 3 == 0))
            echo '<div class="row justify-content-left">';

    ?><div class="tools-edit-image">
            <?php if ($count > 1):  ?>
                <a id="<?= $images[$i]['id'];  ?>" title="Remove this picture" class="delete">X
                </a>
            <?php endif; ?>
        </div>
        <?= Html::img($images[$i]['imageSrc'], ['alt' => "", 'id' => 'pimg-' . ($i + 1), 'class' => 'card-img-top img-fullsize']); ?>

    <?php $i++;
        if (($count > 1) && ($i % 3 == 0))
            echo '</div>';
    }
    ?>
</div>
<div class="card h-100 shadow border-0 pl-3">
    <div class="card-header">
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <!--= Html::a(Yii::t('app', '<i class="fas fa-plus"></i> Create {0}', Yii::t('app', 'Product')), ['create'], ['class' => 'btn btn-success btn-flat float-right']) ?-->
        </div>
    </div>
    <?= ListView::widget([
        // 'tableOptions' => ['class' => 'table table-hover text-nowrap'],
        // 'filterPosition' => 'header',
        'dataProvider' => $dataProvider,
        'options' => [
            'class' => 'row gx-5'
        ],
        /* 'pager'=>[
            'linkOptions' => ['class'=>'pagination justify-content-center']
            ],*/
        'pager' => [
            'class' => yii\bootstrap5\LinkPager::class,
            // 'options' => ['class'=>'pagination justify-content-center'],
            // 'linkContainerOptions' => ['class' => 'page-item p-2 rounded']
        ],
        'itemOptions' => [
            'class' => 'col'
        ],

        'layout' => '<div class="row text-center p-3">{pager}</div><div class="row p-3">{items}</div>{pager}',
        'itemView' => '_product_item',
        'options' => [
            'data' => [
                'sortable' => 1,
                'sortable-url' => Url::to(['sorting']),
            ]
        ],
    ]); ?>
</div>
<?php Pjax::end() ?>