<?php

use yii\db\Migration;

class m170223_113221_addBlameableBehavior extends Migration
{
    public function up()
    {
        $this->addColumn('{{%image_manager}}', 'image_manager_create_account', $this->integer(10)->unsigned()->null()->defaultValue(null));
        $this->addColumn('{{%image_manager}}', 'image_manager_update_account', $this->integer(10)->unsigned()->null()->defaultValue(null));
    }

    public function down()
    {
    	$this->dropColumn('{{%image_manager}}', 'image_manager_create_account');
    	$this->dropColumn('{{%image_manager}}', 'image_manager_update_account');
    }
}
