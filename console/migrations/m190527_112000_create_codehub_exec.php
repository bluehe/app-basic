<?php

use yii\db\Migration;

/**
 * Handles the creation of table `codehub_exec`.
 */
class m190527_112000_create_codehub_exec extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%codehub_exec}}';
        $codehubTable = '{{%corporation_codehub}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="仓库执行表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'codehub_id' => $this->integer()->comment('仓库ID'),
            'user_id' => $this->integer()->comment('用户ID'),
            'updated_at' => $this->integer()->comment('执行时间'),
            'type' => $this->smallInteger()->comment('执行类型'),
            'stat' => $this->smallInteger()->comment('执行结果'),
            "FOREIGN KEY ([[codehub_id]]) REFERENCES {$codehubTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE", 
            "FOREIGN KEY ([[user_id]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%codehub_exec}}');
    }

}
