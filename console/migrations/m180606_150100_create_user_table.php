<?php

use yii\db\Migration;

class m180606_150100_create_user_table extends Migration
{
    public function up()
    {
        $table = '{{%user}}';
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT="用户表"';
        }

        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'username' => $this->string(),
            'auth_key' => $this->string(32),
            'password_hash' => $this->string(),
            'password_reset_token' => $this->string()->unique(),
            'nickname' => $this->string(32),
            'email' => $this->string(64),
            'tel' => $this->string(64),
            'avatar' => $this->string(),
            'gender' => $this->string(8),
            'role'=>$this->string(8),
            'point'=>$this->integer()->notNull()->defaultValue(0),
            'user_color'=>$this->string(8),
            'project'=>$this->smallInteger()->notNull()->defaultValue(0),
            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'last_login' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
        
        $this->insert($table, ['id' => 1, 'username' => 'admin', 'auth_key' => 'BXVEZBxBX8IfnV2ZvZteYALorDvJ7JK3', 'password_hash' => '$2y$13$vmgY.lIfJs2KkjJUnXFDcOzbxvByXmuUbsIkC2E9MSCfKAb08E7qO', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>NULL,'point'=>0,'user_color'=>NULL,'project'=>0, 'status' => 10,  'last_login' => 1482391032,'created_at' => 1482391032, 'updated_at' => 1485054242]);
        
        $this->insert($table, ['id' => 2, 'username' => 'testob1', 'auth_key' => 'iGaRftf9b3bPLmNTQtf6bbE9wasDXptI', 'password_hash' => '$2y$13$SHNctL1i7U2Zu5mpTqKKBeMNjiLwqRz8ASm43fjYceGxSFLd8wVDi', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'ob_data','point'=>0,'user_color'=>NULL,'project'=>0, 'status' => 10,  'last_login' => 1535615879,'created_at' => 1535615880, 'updated_at' => 1535615880]);
        $this->insert($table, ['id' => 3, 'username' => 'testob2', 'auth_key' => 'NcZa9B95P15yMQIOmmyGqsPkle9ETuZh', 'password_hash' => '$2y$13$DUqNKQyOyDhi7/rKl7SXOOhj7xi23btNZ8qqx3wAgQkgn.xAjwq92', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'ob','point'=>0,'user_color'=>NULL,'project'=>0, 'status' => 10,  'last_login' => 1535615991,'created_at' => 1535615992, 'updated_at' => 1535615992]);
        $this->insert($table, ['id' => 4, 'username' => 'testob3', 'auth_key' => 'gs1EbC7vKIjZprAMqW2aa8Ob6IqSEqid', 'password_hash' => '$2y$13$.XI6oF9Zs5lmZKWrvqC3Tu9XEVxthZqgFjcq200wUVgqdRrb4h67a', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'ob','point'=>0,'user_color'=>NULL,'project'=>0, 'status' => 10,  'last_login' => 1535616071,'created_at' => 1535616072, 'updated_at' => 1535616072]);
        $this->insert($table, ['id' => 5, 'username' => 'testsa1', 'auth_key' => 'u6xvNV7XV_IQf0ge6VhztkRQ_zhB3R0d', 'password_hash' => '$2y$13$s.V3MIC9boc3GDuk5huo2erhccp.Wfbkn4tO7Jdo8vwqWbCtLz4E6', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'sa','point'=>0,'user_color'=>'d9389b','project'=>0, 'status' => 10,  'last_login' => 1535616102,'created_at' => 1535616103, 'updated_at' => 1535616103]);
        $this->insert($table, ['id' => 6, 'username' => 'testsa2', 'auth_key' => 'WugSPm5sbr9j9nGNB1Cli08_bFVm2XEy', 'password_hash' => '$2y$13$F3Ytb6/2R7PQu/fzx4wx1.3NrS2tWU1.GYtxbx/mbc2W4oFzsgmQy', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'sa','point'=>0,'user_color'=>'d9389b','project'=>0, 'status' => 10,  'last_login' => 1535616117,'created_at' => 1535616118, 'updated_at' => 1535616118]);
        $this->insert($table, ['id' => 7, 'username' => 'testbd1', 'auth_key' => 'El4lGMW5HlJkClPj8ehsFn8gWGFFPG84', 'password_hash' => '$2y$13$IFatGIsi.k7DhNfmpM4AnuDIRfRaCGFu.gvTq0oQKreJvxnUCa9ru', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'bd','point'=>0,'user_color'=>'d9389b','project'=>0, 'status' => 10,  'last_login' => 1535616194,'created_at' => 1535616195, 'updated_at' => 1535616195]);
        $this->insert($table, ['id' => 8, 'username' => 'testbd2', 'auth_key' => '_oAgd0I2_NPb6J1zov0Dm2ZFsYVCCUgP', 'password_hash' => '$2y$13$9CxyZFCDXcyVqldKxCNvvOXDi7Yddl5I2VPlZrJxYhqsE7KcdTH1u', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'bd','point'=>0,'user_color'=>'d9389b','project'=>0, 'status' => 10,  'last_login' => 1535616283,'created_at' => 1535616284, 'updated_at' => 1535616284]);
        $this->insert($table, ['id' => 9, 'username' => 'testbd3', 'auth_key' => 'UMnSgpPqB-uje0_O9BQMpAwx5CsjbZu7', 'password_hash' => '$2y$13$BCfXkCRk6qj8KXmFohU24uo6ioqtA89QkI3XY2z9Z3K6dvmSa8uai', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'bd','point'=>0,'user_color'=>'d9389b','project'=>0, 'status' => 10,  'last_login' => 1535616298,'created_at' => 1535616299, 'updated_at' => 1535616299]);
        $this->insert($table, ['id' => 10, 'username' => 'testpm', 'auth_key' => 'djZ9baFUYeYRDGZf2xKBTn89yPVIv7iy', 'password_hash' => '$2y$13$oZCSXLNJcj4yJnCwxkcLeOByfnsOVHIcnccaZYzEpFoYCoPHXqCUa', 'password_reset_token' => NULL,'nickname'=>NULL, 'email' => NULL,'tel'=>NULL,'avatar'=>NULL,'gender'=>NULL,'role'=>'pm','point'=>0,'user_color'=>'d9389b','project'=>0, 'status' => 10,  'last_login' => 1535616317,'created_at' => 1535616318, 'updated_at' => 1535616318]);
            
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
