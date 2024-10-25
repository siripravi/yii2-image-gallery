<div class="card-body w-50">
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

<?php if($count > 1) :?>
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
        'itemView' => '_image_item',
        'options' => [
            'data' => [
                'sortable' => 1,
                'sortable-url' => Url::to(['sorting']),
            ]
        ],
    ]); ?>
</div>
<?php endif; ?>
