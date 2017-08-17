<?php

namespace common\modules\image;


use Yii;

/**
 * image module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'common\modules\image\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // initialize the module with the configuration loaded from config.php
        Yii::configure($this, require(__DIR__ . '/config.php'));
    }
}
