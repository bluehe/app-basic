<?php

use yii\db\Migration;

/**
 * Handles the creation of table `field`.
 */
class m180919_163402_create_field_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%field}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="字段表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'parent' => $this->integer(),
            'code' => $this->string(32),
            'name' => $this->string(32)->notNull()->unique(),
            'type' => $this->integer(),
            "FOREIGN KEY ([[parent]]) REFERENCES {$table}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
          
        ], $tableOptions);
            
//        $this->createIndex('code',$table, ['code']);
              
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%field}}');
    }

}
