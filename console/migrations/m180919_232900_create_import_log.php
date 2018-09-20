<?php

use yii\db\Migration;

/**
 * Handles the creation of table `import_log`.
 */
class m180919_232900_create_import_log extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%import_log}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="导入记录表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'name' => $this->string(64),
            'patch' => $this->string(64),
            'statistics_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'stat' => $this->smallInteger()->notNull()->defaultValue(1),                 
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%import_log}}');
    }

}
