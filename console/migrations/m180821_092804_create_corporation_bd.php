<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporation_bd`.
 */
class m180821_092804_create_corporation_bd extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation_bd}}';
        $corporationTable = '{{%corporation}}';
        $bdTable = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业BD关联表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'corporation_id' => $this->integer()->notNull(),
            'bd_id' => $this->integer()->notNull(),
            'start_time' => $this->integer(),
            'end_time' => $this->integer(),
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[bd_id]]) REFERENCES {$bdTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);

       
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation_bd}}');
    }

}
