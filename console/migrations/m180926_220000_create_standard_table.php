<?php

use yii\db\Migration;

/**
 * Handles the creation of table `standard`.
 */
class m180926_220000_create_standard_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%standard}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="活跃标准表"';
        }
        $this->createTable($table, [
//            'id' => $this->primaryKey(),
            'type' => $this->integer(),
            'field' => $this->string(32),
            'value' => $this->string(32),
            'PRIMARY KEY (type, field)',
        ], $tableOptions);
            
              
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%standard}}');
    }

}
