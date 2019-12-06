<?php

use yii\db\Migration;

/**
 * Handles the creation for table `ImageManager`.
 */
class m160622_085710_create_image_manager_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
		//ImageManager: create table
        $this->createTable('{{%image_manager}}', [
            'image_manager_id' => $this->primaryKey(),
			'image_manager_filename' => $this->string(128)->notNull(),
			'image_manager_filehash' => $this->string(32)->notNull(),
			'image_manager_create_datetime' => $this->datetime()->notNull(),
			'image_manager_update_datetime' => $this->datetime(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%image_manager}}');
    }
}
