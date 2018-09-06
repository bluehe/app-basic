<?php

use yii\db\Migration;
use project\models\Corporation;

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

        //企业状态定时任务
        $corporation_table = '{{%corporation}}';
        $corporation_meal_table = '{{%corporation_meal}}';
        $corporation_stat_table = '{{%corporation_stat}}';
        $stat_o = Corporation::STAT_OVERDUE;
        $stat_al = Corporation::STAT_ALLOCATE;
        $stat_ag = Corporation::STAT_AGAIN;
        
        
//        $this->execute("set GLOBAL event_scheduler = ON;");
        $this->execute("DROP EVENT IF EXISTS `event_allocate_overdue`;");
        $this->execute("CREATE EVENT `event_allocate_overdue` ON SCHEDULE EVERY 1 DAY STARTS '2018-09-01 00:00:00'
            ENABLE
            DO
            BEGIN
                INSERT INTO {$corporation_stat_table}(corporation_id,stat,created_at) SELECT corporation_id,{$stat_o},unix_timestamp(now()) FROM {$corporation_meal_table} LEFT JOIN {$corporation_table} ON {$corporation_meal_table}.corporation_id={$corporation_table}.id  WHERE (stat={$stat_al} OR stat={$stat_ag}) GROUP BY corporation_id HAVING MAX(end_time)<unix_timestamp(now());
                UPDATE {$corporation_table} SET stat={$stat_o} WHERE (stat={$stat_al} OR stat={$stat_ag}) AND id IN (SELECT corporation_id FROM {$corporation_meal_table} GROUP BY corporation_id HAVING MAX(end_time)<unix_timestamp(now()));              
                UPDATE {$table} SET exc_at=unix_timestamp(now()) WHERE name='event_allocate_overdue';
            END");
        $this->insert($table, ['id' => 1, 'name' => 'event_allocate_overdue', 'title' => '下拨过期', 'start_at' => 1535731200, 'end_at' => NULL, 'interval_time' => 86400, 'content' => "INSERT INTO {$corporation_stat_table}(corporation_id,stat,created_at) SELECT corporation_id,{$stat_o},unix_timestamp(now()) FROM {$corporation_meal_table} LEFT JOIN {$corporation_table} ON {$corporation_meal_table}.corporation_id={$corporation_table}.id  WHERE (stat={$stat_al} OR stat={$stat_ag}) GROUP BY corporation_id HAVING MAX(end_time)<unix_timestamp(now());UPDATE {$corporation_table} SET stat={$stat_o} WHERE (stat={$stat_al} OR stat={$stat_ag}) AND id IN (SELECT corporation_id FROM {$corporation_meal_table} GROUP BY corporation_id HAVING MAX(end_time)<unix_timestamp(now()));", 'exc_at' => NULL, 'stat' => 1]);

    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%crontab}}');
//        $this->execute('DROP EVENT IF EXISTS `event_repairevaluate`;');
    }

}
