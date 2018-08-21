<?php

use yii\db\Migration;

/**
 * Handles the creation of table `industry`.
 */
class m180821_092801_create_industry_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%industry}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="行业表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer(),
            'name' => $this->string(32)->notNull(),
            'industry_sort' =>  $this->smallInteger()->notNull()->defaultValue(10),
            "FOREIGN KEY ([[parent_id]]) REFERENCES {$table}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions); 
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%industry}}');
    }

}
