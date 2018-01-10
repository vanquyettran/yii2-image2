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
            'id',
            [
                'attribute' => '',
                'format' => 'raw',
                'value' => function (Image $model) {
                    return $model->img('100x100');
                }
            ],
            'name',
            'path',
            'file_basename',
            'file_extension',
            'mime_type',
            'aspect_ratio',
            'created_time:datetime',
            'updated_time:datetime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
