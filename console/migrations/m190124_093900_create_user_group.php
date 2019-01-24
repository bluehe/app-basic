<?php

use yii\db\Migration;

/**
 * Handles the creation of table `group_user`.
 */
class m190124_093900_create_user_group extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%user_group}}';
        $groupTable = '{{%group}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="项目用户表"';
        }
        $this->createTable($table, [
            'user_id' => $this->integer()->notNull(),
            'group_id' => $this->integer()->notNull(),           
            'PRIMARY KEY (group_id, user_id)',
            "FOREIGN KEY ([[group_id]]) REFERENCES {$groupTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[user_id]]) REFERENCES {$userTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%user_group}}');
    }

}
