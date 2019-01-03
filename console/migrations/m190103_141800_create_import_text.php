<?php

use yii\db\Migration;

/**
 * Handles the creation of table `import_data`.
 */
class m190103_141800_create_import_text extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%import_text}}';
        $logTable = '{{%import_log}}';      
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="导入文本表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'log_id' => $this->integer(),            
            'data' => $this->text(),
            "FOREIGN KEY ([[log_id]]) REFERENCES {$logTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",                 
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%import_text}}');
    }

}
