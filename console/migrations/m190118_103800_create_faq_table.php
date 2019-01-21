<?php

use yii\db\Migration;

/**
 * Handles the creation of table `import_data`.
 */
class m190118_103800_create_faq_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%faq}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="知识库表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'type' => $this->integer(),
            'question' => $this->text(),
            'question_extend' => $this->text(),
            'answer' => $this->text(),
            'answer_uid' => $this->integer(),
            'created_uid' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),                
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%faq}}');
    }

}
