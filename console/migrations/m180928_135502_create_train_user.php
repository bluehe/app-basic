<?php

use yii\db\Migration;

class m180928_135502_create_train_user extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%train_user}}';
        $userTable = '{{%user}}';
        $trainTable = '{{%train}}';

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="培训人员表"';
        }
        $this->createTable($table, [
            'train_id' => $this->integer()->notNull()->comment('培训ID'),
            'user_id' => $this->integer()->notNull()->comment('人员ID'),
            'tuser_sort' =>  $this->smallInteger(),
            'PRIMARY KEY (train_id, user_id)',            
            "FOREIGN KEY ([[train_id]]) REFERENCES {$trainTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[user_id]]) REFERENCES {$userTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%train_user}}');
    }

}
