<?php

use yii\db\Migration;

/**
 * Handles the creation of table `import_data`.
 */
class m180919_233000_create_import_data extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%import_data}}';
        $logTable = '{{%import_log}}';
        $corporationTable = '{{%corporation}}';
        $fieldTable = '{{%field}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="导入数据表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'log_id' => $this->integer(),
            'corporation_id' => $this->integer(),
            'field_id' => $this->integer(),
            'data' => $this->integer()->notNull()->defaultValue(0),
            "FOREIGN KEY ([[log_id]]) REFERENCES {$logTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[field_id]]) REFERENCES {$fieldTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",           
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%import_data}}');
    }

}
