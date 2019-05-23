<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporation_codehub`.
 */
class m190520_112000_create_corporation_codehub extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation_codehub}}';
        $corporationTable = '{{%corporation}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业仓库表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'corporation_id' => $this->integer()->comment('企业ID'),
            'name' => $this->string(32)->comment('名称'),
            'project_uuid' => $this->string(32)->comment('项目UUID'),
            'repository_uuid' => $this->string(32)->comment('仓库UUID'),
            'https_url' => $this->string(128)->comment('仓库URL'),
            'username' => $this->string(32)->comment('用户名'),
            'password' => $this->string(32)->comment('密码'),
            'ci' => $this->smallInteger()->notNull()->defaultValue(1)->comment('持续集成'),
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",                 
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation_codehub}}');
    }

}
