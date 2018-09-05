<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pickup`.
 */
class m180905_100600_create_column_setting extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%column_setting}}';
        $userTable = '{{%user}}';

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="列显示表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'type' => $this->string(),
            'uid' => $this->integer(),
            'content' => $this->text(),           
            "FOREIGN KEY ([[uid]]) REFERENCES {$userTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%column_setting}}');
    }

}
