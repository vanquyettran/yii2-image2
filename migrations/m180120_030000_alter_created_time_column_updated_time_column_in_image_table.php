<?php

use yii\db\Migration;

/**
 * Handles altering status column in table `user`.
 */
class m180120_030000_alter_created_time_column_updated_time_column_in_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->alterColumn('image', 'created_time', $this->dateTime()->notNull());
        $this->alterColumn('image', 'updated_time', $this->dateTime()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->alterColumn('image', 'created_time', $this->integer()->notNull());
        $this->alterColumn('image', 'updated_time', $this->integer()->notNull());
    }
}
