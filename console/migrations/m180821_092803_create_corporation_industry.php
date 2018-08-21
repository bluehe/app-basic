<?php

use yii\db\Migration;

/**
 * Handles the creation of table `corporation_industry`.
 */
class m180821_092803_create_corporation_industry extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%corporation_industry}}';
        $corporationTable = '{{%corporation}}';
        $industryTable = '{{%industry}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业行业关联表"';
        }
        $this->createTable($table, [
            'corporation_id' => $this->integer()->notNull(),
            'industry_id' => $this->integer()->notNull(),
            'PRIMARY KEY (corporation_id, industry_id)',
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[industry_id]]) REFERENCES {$industryTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);

       
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%corporation_industry}}');
    }

}
