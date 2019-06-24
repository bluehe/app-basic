<?php

use yii\db\Migration;

/**
 * Handles the creation of table `health_log`.
 */
class m190610_152700_create_health_log extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%health_log}}';
        $groupTable = '{{%group}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="导入记录表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->comment('项目'),
            'name' => $this->string(64),
            'patch' => $this->string(64),
            'statistics_at' => $this->integer(),
            'uid' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'stat' => $this->smallInteger()->notNull()->defaultValue(1), 
            "FOREIGN KEY ([[group_id]]) REFERENCES {$groupTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[uid]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%health_log}}');
    }

}
