<?php

use yii\db\Migration;

/**
 * Handles the creation of table `crontab`.
 */
class m180606_161800_create_crontab_table extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%crontab}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="定时任务表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'start_at' => $this->integer()->notNull(),
            'end_at' => $this->integer(),
            'interval_time' => $this->integer(),
            'content' => $this->text()->notNull(),
            'exc_at' => $this->integer(),
            'stat' => $this->smallInteger()->notNull()->defaultValue(1),
                ], $tableOptions);

    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%crontab}}');
//        $this->execute('DROP EVENT IF EXISTS `event_repairevaluate`;');
    }

}
