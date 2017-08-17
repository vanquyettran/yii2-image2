<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \common\modules\image\models\Image;

/* @var $this yii\web\View */
/* @var $searchModel common\modules\image\models\ImageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Images';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="image-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php echo Html::a('Create Image', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

//            'id',
            [
                'attribute' => 'IMG',
                'format' => 'raw',
                'value' => function (Image $model) {
                    return $model->img('30x30');
                }
            ],
//            'creator_id',
//            'updater_id',
            'name',
//            'path',
            // 'file_name',
             'file_basename',
            // 'file_extension',
            // 'resize_labels',
            // 'string_data',
//             'mime_type',
//            [
//                'attribute' => 'active',
//                'format' => 'raw',
//                'value' => function ($model) {
//                    if ($model->active) {
//                        return '<span class="label label-success">active</span>';
//                    }
//                    return '<span class="label label-default">inactive</span>';
//                }
//            ],
            // 'status',
             'aspect_ratio',
//             'create_time:datetime',
             'update_time:datetime',
             'active:boolean',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
