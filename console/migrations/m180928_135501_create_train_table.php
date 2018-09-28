<?php

use yii\db\Migration;

class m180928_135501_create_train_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%train}}';
        $userTable = '{{%user}}';
        $corporationTable = '{{%corporation}}';

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="培训记录表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'uid' => $this->integer(),           
            'created_at' => $this->integer()->notNull(),           
            'updated_at' => $this->integer()->notNull(),
            'train_type' => $this->string(),
            'corporation_id' => $this->integer(),
            'train_name' => $this->string(),
            'train_address' => $this->string(),           
            'train_start'=>$this->integer(),
            'train_end'=>$this->integer(),
            'other_people'=>$this->string(),
            'train_num'=>$this->integer(),           
            'train_result'=>$this->text(),           
            'train_note' => $this->text(),
            'reply_uid' => $this->integer(),
            'reply_at' => $this->integer(),
            'train_stat' => $this->smallInteger()->notNull()->defaultValue(1),
            "FOREIGN KEY ([[uid]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[reply_uid]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
                ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%train}}');
    }

}
