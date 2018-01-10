<?php

use yii\db\Migration;

/**
 * Handles the creation of table `image`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `user`
 */
class m170730_002928_create_image_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('image', [
            'id' => $this->primaryKey(),
            'file_basename' => $this->string()->notNull()->unique(),
            'path' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'file_extension' => $this->string()->notNull(),
            'mime_type' => $this->string()->notNull(),
            'quality' => $this->smallInteger(3)->notNull(),
            'aspect_ratio' => $this->string(32)->notNull(),
            'width' => $this->integer()->notNull(),
            'height' => $this->integer()->notNull(),
            'resize_labels' => $this->string(2047)->notNull(),
            'created_time' => $this->integer()->notNull(),
            'updated_time' => $this->integer()->notNull(),
            'creator_id' => $this->integer()->notNull(),
            'updater_id' => $this->integer()->notNull(),
        ], $tableOptions);

        // creates index for column `creator_id`
        $this->createIndex(
            'idx-image-creator_id',
            'image',
            'creator_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-image-creator_id',
            'image',
            'creator_id',
            'user',
            'id',
            'RESTRICT'
        );

        // creates index for column `updater_id`
        $this->createIndex(
            'idx-image-updater_id',
            'image',
            'updater_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-image-updater_id',
            'image',
            'updater_id',
            'user',
            'id',
            'RESTRICT'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-image-creator_id',
            'image'
        );

        // drops index for column `creator_id`
        $this->dropIndex(
            'idx-image-creator_id',
            'image'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-image-updater_id',
            'image'
        );

        // drops index for column `updater_id`
        $this->dropIndex(
            'idx-image-updater_id',
            'image'
        );

        $this->dropTable('image');
    }
}
