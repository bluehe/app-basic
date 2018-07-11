<?php

use yii\db\Migration;

class m180705_160900_create_recommend extends Migration {

    public function up() {
        $table = '{{%recommend}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="推荐表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'name' => $this->string(4),            
            'url' => $this->string(),
            'img' => $this->string(),            
            'click_num' => $this->integer(),  
            'sort_order' => $this->smallInteger(),
            'stat' => $this->smallInteger(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
                ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%recommend}}');
    }

}
