<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporation_project`.
 */
class m190516_140200_create_corporation_project extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation_project}}';
        $corporationTable = '{{%corporation}}';
        $accountTable = '{{%corporation_account}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业项目表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'corporation_id' => $this->integer()->comment('企业ID'),           
            'name' => $this->string(32)->comment('名称'),
            'description' => $this->string(32)->comment('项目描述'),
            'project_uuid' => $this->string(32)->comment('项目UUID'),
            'add_type' => $this->smallInteger()->notNull()->defaultValue(1)->comment('添加方式'),
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",                 
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation_project}}');
    }

}
