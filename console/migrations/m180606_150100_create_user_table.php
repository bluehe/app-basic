<?php

use yii\db\Migration;

class m180606_150100_create_user_table extends Migration
{
    public function up()
    {
        $table = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="用户表"';
        }

        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'username' => $this->string(),
            'auth_key' => $this->string(32),
            'password_hash' => $this->string(),
            'password_reset_token' => $this->string()->unique(),
            'nickname' => $this->string(32),
            'email' => $this->string(64),
            'tel' => $this->string(64),
            'avatar' => $this->string(),
            'gender' => $this->string(8),
            'role'=>$this->string(8),
            'point'=>$this->integer(),
            'project'=>$this->smallInteger()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'last_login' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        
        $this->insert($table, ['id' => 1, 'username' => 'admin', 'auth_key' => 'BXVEZBxBX8IfnV2ZvZteYALorDvJ7JK3', 'password_hash' => '$2y$13$vmgY.lIfJs2KkjJUnXFDcOzbxvByXmuUbsIkC2E9MSCfKAb08E7qO', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>NULL,'point'=>0,'project'=>0, 'status' => 10,  'last_login' => 1482391032,'created_at' => 1482391032, 'updated_at' => 1485054242]);
            
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
