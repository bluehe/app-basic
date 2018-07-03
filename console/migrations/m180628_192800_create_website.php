<?php

use yii\db\Migration;

class m180628_192800_create_website extends Migration {

    public function up() {
        $table = '{{%website}}';
        $userTable = '{{%user}}';
        $categoryTable = '{{%category}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="网址表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'cid' => $this->integer(),
            'title' => $this->string(),
            'url' => $this->string(),
            'host' => $this->string(),
            'note' => $this->string(),
            'sort_order' => $this->smallInteger(),
            'share_status' => $this->smallInteger(),
            'share_id' => $this->integer(),
            'collect_num' => $this->integer(),  
            'click_num' => $this->integer(),  
            'is_open' => $this->smallInteger(),
            'stat' => $this->smallInteger(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),           
            "FOREIGN KEY ([[share_id]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[cid]]) REFERENCES {$categoryTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%website}}');
    }

}
