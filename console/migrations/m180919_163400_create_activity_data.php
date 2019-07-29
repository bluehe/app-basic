<?php

use yii\db\Migration;

/**
 * Handles the creation of table `activity_data`.
 */
class m180919_163400_create_activity_data extends Migration {

    /**
     * @inheritdoc
     */
    public function up() {
        $table = '{{%activity_data}}';
        $corporationTable = '{{%corporation}}';
        $groupTable = '{{%group}}';
      
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="企业数据表"';
        }
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->comment('项目'),
            'corporation_id' => $this->integer()->notNull(),
            'statistics_time' => $this->integer()->notNull(),
            
            'devcloud_pv' => $this->integer()->notNull()->defaultValue(0),
            
            'projectman_projectcount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_membercount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_issuecount' => $this->integer()->notNull()->defaultValue(0),
            'projectman_wiki' => $this->integer()->notNull()->defaultValue(0),
            'projectman_docman' => $this->integer()->notNull()->defaultValue(0),
//            'projectman_versioncount' => $this->integer()->notNull()->defaultValue(0),           
//            'projectman_storagecount' => $this->double()->notNull()->defaultValue(0),
//            'projectman_usercount' => $this->integer()->notNull()->defaultValue(0),
                        
            'codehub_repositorycount' => $this->integer()->notNull()->defaultValue(0),
            'codehub_commitcount' => $this->integer()->notNull()->defaultValue(0),
            'codehub_repositorysize' => $this->double()->notNull()->defaultValue(0),
//            'codehub_all_usercount' => $this->integer()->notNull()->defaultValue(0),
                        
            'pipeline_assignmentscount' => $this->integer()->notNull()->defaultValue(0),
            'pipeline_elapse_time' => $this->float()->notNull()->defaultValue(0),
//            'pipeline_usercount' => $this->integer()->notNull()->defaultValue(0),
//            'pipeline_pipecount' => $this->integer()->notNull()->defaultValue(0),
//            'pipeline_executecount' => $this->integer()->notNull()->defaultValue(0),
                                  
            'codecheck_taskcount' => $this->integer()->notNull()->defaultValue(0),
            'codecheck_codelinecount' => $this->integer()->notNull()->defaultValue(0),            
            'codecheck_execount' => $this->integer()->notNull()->defaultValue(0),
//            'codecheck_usercount' => $this->integer()->notNull()->defaultValue(0),
//            'codecheck_issuecount' => $this->integer()->notNull()->defaultValue(0),
                       
            'codeci_buildcount' => $this->integer()->notNull()->defaultValue(0),            
            'codeci_buildtotaltime' => $this->float()->notNull()->defaultValue(0),
//            'codeci_usercount' => $this->integer()->notNull()->defaultValue(0),
//            'codeci_allbuildcount' => $this->integer()->notNull()->defaultValue(0),
                       
            'testman_casecount' => $this->integer()->notNull()->defaultValue(0),
            'testman_execasecount' => $this->integer()->notNull()->defaultValue(0),
//            'testman_usercount' => $this->integer()->notNull()->defaultValue(0),
//            'testman_totalexecasecount' => $this->integer()->notNull()->defaultValue(0),
                        
            'deploy_envcount' => $this->integer()->notNull()->defaultValue(0),
            'deploy_execount' => $this->integer()->notNull()->defaultValue(0),
//            'deploy_usercount' => $this->integer()->notNull()->defaultValue(0),
//            'deploy_vmcount' => $this->float()->notNull()->defaultValue(0),
            
            'releaseman_uploadcount' => $this->integer()->notNull()->defaultValue(0),
            'releaseman_downloadcount' => $this->integer()->notNull()->defaultValue(0),
               
            "FOREIGN KEY ([[corporation_id]]) REFERENCES {$corporationTable}([[id]]) ON DELETE CASCADE ON UPDATE CASCADE",
            "FOREIGN KEY ([[group_id]]) REFERENCES {$groupTable}([[id]]) ON DELETE SET NULL ON UPDATE CASCADE",
           
           
                ], $tableOptions);
            
            $this->createIndex('statistics_time',$table, ['statistics_time']); 
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $this->dropTable('{{%activity_data}}');
    }

}
