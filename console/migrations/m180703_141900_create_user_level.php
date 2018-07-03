<?php

use yii\db\Migration;

class m180703_141900_create_user_level extends Migration {

    public function up() {
       $table = '{{%user_level}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="用户等级表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'level' => $this->integer(),
            'point' => $this->integer(),
                ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%user_level}}');
    }

}
