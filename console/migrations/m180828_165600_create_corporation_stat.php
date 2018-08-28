<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporation_bd`.
 */
class m180828_165600_create_corporation_stat extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation_stat}}';
        $corporationTable = '{{%corporation}}';
        $userTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业状态记录表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'corporation_id' => $this->integer()->notNull(),
            'stat'=>$this->integer()->notNull(),
            'user_id' => $this->integer(),
            'created_at' => $this->integer(),
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[user_id]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);

       
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation_stat}}');
    }

}
