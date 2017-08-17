<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ImageSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="image-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'creator_id') ?>

    <?= $form->field($model, 'updater_id') ?>

    <?= $form->field($model, 'file_basename') ?>

    <?= $form->field($model, 'path') ?>

    <?php // echo $form->field($model, 'name') ?>

    <?php // echo $form->field($model, 'file_extension') ?>

    <?php // echo $form->field($model, 'mime_type') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'resize_labels') ?>

    <?php // echo $form->field($model, 'encode_data') ?>

    <?php // echo $form->field($model, 'active') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'update_time') ?>

    <?php // echo $form->field($model, 'sort_order') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
