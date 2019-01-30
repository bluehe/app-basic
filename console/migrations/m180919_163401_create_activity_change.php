<?php

use yii\db\Migration;

/**
 * Handles the creation of table `activity`.
 */
class m180919_163401_create_activity_change extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%activity_change}}';
        $userTable = '{{%user}}';
        $corporationTable = '{{%corporation}}';
        $groupTable = '{{%group}}';
      
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="数据差额表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->comment('项目'),
            'start_time' => $this->integer()->notNull(),
            'end_time' => $this->integer()->notNull(),
            'bd_id' => $this->integer(),
            'corporation_id' => $this->integer()->notNull(),
            'type' => $this->integer()->notNull(),
            'is_act' => $this->integer()->notNull()->defaultValue(0),
            'act_trend' => $this->integer()->notNull()->defaultValue(0),
            'health' => $this->integer()->notNull()->defaultValue(-1),
            'h_h' => $this->float()->notNull()->defaultValue(0),
            'h_c' => $this->float()->notNull()->defaultValue(0),
            'h_i' => $this->float()->notNull()->defaultValue(0),
            'h_a' => $this->float()->notNull()->defaultValue(0),
            'h_r' => $this->float()->notNull()->defaultValue(0),
            'h_v' => $this->float()->notNull()->defaultValue(0),
            'h_d' => $this->float()->notNull()->defaultValue(0),
            //'h_membercount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_usercount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_projectcount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_membercount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_versioncount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_issuecount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_storagecount' => $this->double()->notNull()->defaultValue(0),
            
            'codehub_all_usercount' => $this->integer()->notNull()->defaultValue(0),
            'codehub_repositorycount' => $this->integer()->notNull()->defaultValue(0),
            'codehub_commitcount' => $this->integer()->notNull()->defaultValue(0),
            'codehub_repositorysize' => $this->double()->notNull()->defaultValue(0),
            
            'pipeline_usercount' => $this->integer()->notNull()->defaultValue(0),
            'pipeline_pipecount' => $this->integer()->notNull()->defaultValue(0),
            'pipeline_executecount' => $this->integer()->notNull()->defaultValue(0),
            'pipeline_elapse_time' => $this->float()->notNull()->defaultValue(0),
            
            'codecheck_usercount' => $this->integer()->notNull()->defaultValue(0),
            'codecheck_taskcount' => $this->integer()->notNull()->defaultValue(0),
            'codecheck_codelinecount' => $this->integer()->notNull()->defaultValue(0),
            'codecheck_issuecount' => $this->integer()->notNull()->defaultValue(0),
            'codecheck_execount' => $this->integer()->notNull()->defaultValue(0),
            
            'codeci_usercount' => $this->integer()->notNull()->defaultValue(0),
            'codeci_buildcount' => $this->integer()->notNull()->defaultValue(0),
            'codeci_allbuildcount' => $this->integer()->notNull()->defaultValue(0),
            'codeci_buildtotaltime' => $this->float()->notNull()->defaultValue(0),
            
            'testman_usercount' => $this->integer()->notNull()->defaultValue(0),
            'testman_casecount' => $this->integer()->notNull()->defaultValue(0),
            'testman_totalexecasecount' => $this->integer()->notNull()->defaultValue(0),
            
            'deploy_usercount' => $this->integer()->notNull()->defaultValue(0),
            'deploy_envcount' => $this->integer()->notNull()->defaultValue(0),
            'deploy_execount' => $this->integer()->notNull()->defaultValue(0),
            'deploy_vmcount' => $this->float()->notNull()->defaultValue(0),
               
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[bd_id]]) REFERENCES {$userTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
            "FOREIGN KEY ([[group_id]]) REFERENCES {$groupTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
           
                ], $tableOptions);
            
            $this->createIndex('start_time',$table, ['start_time']); 
            $this->createIndex('end_time',$table, ['end_time']); 
            $this->createIndex('corporation_id',$table, ['corporation_id','start_time','end_time'],true);
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%activity_change}}');
    }

}
