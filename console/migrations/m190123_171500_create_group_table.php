<?php

use yii\db\Migration;

/**
 * Handles the creation of table `group`.
 */
class m190123_171500_create_group_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%group}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="项目表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'name' => $this->string(64)->comment('名称'),
            'title' => $this->string(16)->comment('简称'),
            'area' => $this->string(32)->comment('地域'),
            'address'=>$this->string(128)->comment('地址'),
            'location'=>$this->string(64)->comment('坐标'),
                            
            ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%group}}');
    }

}
