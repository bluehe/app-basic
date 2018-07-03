<?php

use yii\db\Migration;

class m180703_153600_create_user_sign extends Migration {

    public function up() {
        $table = '{{%user_sign}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="用户签到表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'uid' => $this->integer(),
            'y' => $this->string(4),
            'm' => $this->string(2),
            'd' => $this->string(2),
            'series' => $this->integer(),
            'note' => $this->string(),
            'sign_at' => $this->integer(),
            'created_at' => $this->integer(),
            "FOREIGN KEY ([[uid]]) REFERENCES {$userTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%user_sign}}');
    }

}
