<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporation_account`.
 */
class m190513_150600_create_corporation_account extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation_account}}';
        $corporationTable = '{{%corporation}}';      
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业账号表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'corporation_id' => $this->integer(),
            'account_name' => $this->string(32)->comment('账号名'),
            'user_name' => $this->string(32)->comment('用户名'),
            'password' => $this->string(32)->comment('密码'), 
            'domain_id' => $this->string(32)->comment('租户ID'),
            'user_id' => $this->string(32)->comment('用户ID'),
            'is_admin' => $this->smallInteger()->notNull()->defaultValue(1),
            'add_type' => $this->smallInteger()->notNull()->defaultValue(1)->comment('添加方式'),//1、手动;2、固有;3、系统添加
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",                 
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation_account}}');
    }

}
