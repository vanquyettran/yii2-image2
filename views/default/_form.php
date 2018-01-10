<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\modules\image\models\Image;
use yii\helpers\Url;

$module = \common\modules\image\Module::getInstance();

/**
 * @var $this View
 * @var $model Image
 * @var $form ActiveForm
 */

if ($model->isNewRecord) {
    $model->quality = 80;
}
$model->image_name_to_basename = true;
?>
<style>
    #image-preview-wrapper img {
        display: block;
        max-width: 100%;
        max-height: 158px;
        width: auto;
        height: auto;
    }
</style>
<div class="image-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div id="image-preview-wrapper">
                    <?= $model->img() ?>
                </div>
            </div>

            <?= $form->field($model, 'image_file')->fileInput(['accept' => Image::getValidImageExtensions()]) ?>

            <?= $form->field($model, 'image_source', [
                'template' => '<span class="label label-info">OR</span> {label}{input}{error}{hint}'
            ])->textInput(['placeholder' => Yii::t('app', 'If Image Source is filled in, Image File will be ignored')]) ?>

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'file_basename')->textInput(['maxlength' => true]) ?>

        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'input_resize_keys')->dropDownList(
                array_merge(
                    ['' => Yii::t('app', 'Default')],
                    $model->input_resize_keys,
                    array_combine($module->params['input_resize_keys'], $module->params['input_resize_keys'])
                ),
                [
                    'multiple' => 'multiple',
                    'size' => 10,
                ]
            ) ?>

            <div class="form-group">
                <input type="text" id="input-resize-key" class="disable-counter">
                <button type="button" onclick="addInputResizeKey()">ADD</button>
                <script>
                    function addInputResizeKey() {
                        var input = document.getElementById("input-resize-key");
                        var select = document.getElementById("<?= Html::getInputId($model, 'input_resize_keys') ?>");
                        var matches = input.value.match(/(\D*)(\d+)x(\d+)(\D*)/);
                        var value;
                        console.log(matches);
                        if (matches && matches[2] && matches[3]) {
                            value = matches[2] + "x" + matches[3];
                        }
                        if (value && !select.querySelector("option[value='" + value + "']")) {
                            var option = document.createElement("option");
                            option.value = value;
                            option.innerHTML = value;
                            select.appendChild(option);
                        }
                        input.value = "";
                    }
                </script>
            </div>

            <?= $form->field($model, 'image_crop')->checkbox() ?>

            <?= $form->field($model, 'image_name_to_basename')->checkbox() ?>

            <?= $form->field($model, 'quality')->textInput() ?>
        </div>

    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
    var img_preview = document.getElementById("image-preview-wrapper");
    var img_input = document.getElementById("<?= Html::getInputId($model, 'image_file') ?>");
    var img_source = document.getElementById("<?= Html::getInputId($model, 'image_source') ?>");
    var img_source_loaded = false;
    var last_img_src = img_preview.querySelector("img") ? img_preview.querySelector("img").src : "";
    var img_name = document.getElementById("<?= Html::getInputId($model, 'name') ?>");
    var img_file_basename = document.getElementById("<?= Html::getInputId($model, 'file_basename') ?>");
    img_preview.empty = function () {
        while(img_preview.firstChild) {
            img_preview.removeChild(img_preview.firstChild);
        }
    };
    img_source.addEventListener("change", function (event) {
        var image = new Image();
        if (img_source.value) {
            image.src = img_source.value;

            var msg = document.createElement("span");
            msg.className = "text-info";
            msg.innerHTML = "Loading...";
            img_preview.appendChild(msg);
            image.addEventListener("load", function () {
                img_preview.empty();
                img_preview.appendChild(image);
                img_source_loaded = true;
                img_name.value = img_file_basename.value = image.src.replace(/^.*[\\\/]/, '').replace(/\.[^/.]+$/, '');
            });
            image.addEventListener("error", function (event) {
                img_preview.empty();
                msg.className = "text-danger";
                msg.innerHTML = "Cannot load image source.";
                img_preview.appendChild(msg);
                img_source_loaded = false;
            });
        } else {
            image.src = last_img_src;
            img_preview.empty();
            img_preview.appendChild(image);
            img_source_loaded = false;
        }
    });
    img_input.addEventListener("change", function (event) {
        if (img_source_loaded) {
            event.preventDefault();
            return false;
        }
        var reader = new FileReader();
        var file = this.files[0];
        // @TODO: Read image file as URL

        if (reader.readAsDataURL) {
            reader.readAsDataURL(file);
        } else if (reader.readAsDataurl) {
            reader.readAsDataurl(file);
        } else {
            throw "Browser does not support.";
        }

        // @TODO: Append image preview
        var image = new Image();
        reader.addEventListener("load", function () {
            image.src = reader.result;
            img_preview.empty();
            img_preview.appendChild(image);
            last_img_src = image.src;
            img_name.value = img_file_basename.value = file.name.replace(/\.[^/.]+$/, '');
        });
    });
</script>