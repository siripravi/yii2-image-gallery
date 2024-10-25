<?php

use yii\bootstrap5\Dropdown;
use yii\bootstrap5\Button;
use yii\helpers\Url;
use yii\helpers\Html;

?>
<?php
$thumbVer = Yii::$app->gallery->thumbVer;
$modelSrc =  Url::to(['/gallery/default/create', 'id' => $model->id, 'version' => $thumbVer]);
?>
<div class="card" style="width: 18rem;">
    <div class="card-thumbnail">
        <?php if ($model) { ?>
            <img src="<?= $modelSrc; ?>" alt="<?= $model->extension ? $model->extension : $model->slug ?>" title="<?= $model->filename ?>" class="card-img rounded-circle">
        <?php } else { ?>
            <img class="img-fluid" src="<?= $modelSrc ?>" alt="">
        <?php } ?>
    </div>
    <?php if ($multiple):  ?>
        <?php
        echo Html::a("Delete", Url::to('/gallery/default/remove-image?id=' . $model->id), ['class' => 'btn btn-warning']);
        ?>
    <?php endif;  ?>
</div>