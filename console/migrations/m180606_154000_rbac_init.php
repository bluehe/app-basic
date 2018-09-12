<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
use yii\base\InvalidConfigException;
use yii\rbac\DbManager;

/**
 * Initializes RBAC tables
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @since 2.0
 */
class m180606_154000_rbac_init extends \yii\db\Migration {

    /**
     * @throws yii\base\InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager() {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }
        return $authManager;
    }

    /**
     * @return bool
     */
    protected function isMSSQL() {
        return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
    }

    /**
     * @inheritdoc
     */
    public function up() {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($authManager->ruleTable, [
            'name' => $this->string(64)->notNull(),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY (name)',
                ], $tableOptions);

        $this->createTable($authManager->itemTable, [
            'name' => $this->string(64)->notNull(),
            'type' => $this->integer()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY (name)',
            'FOREIGN KEY (rule_name) REFERENCES ' . $authManager->ruleTable . ' (name)' .
            ($this->isMSSQL() ? '' : ' ON DELETE SET NULL ON UPDATE CASCADE'),
                ], $tableOptions);
        $this->createIndex('idx-auth_item-type', $authManager->itemTable, 'type');

        $this->createTable($authManager->itemChildTable, [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY (parent, child)',
            'FOREIGN KEY (parent) REFERENCES ' . $authManager->itemTable . ' (name)' .
            ($this->isMSSQL() ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
            'FOREIGN KEY (child) REFERENCES ' . $authManager->itemTable . ' (name)' .
            ($this->isMSSQL() ? '' : ' ON DELETE CASCADE ON UPDATE CASCADE'),
                ], $tableOptions);

        $this->createTable($authManager->assignmentTable, [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY (item_name, user_id)',
            'FOREIGN KEY (item_name) REFERENCES ' . $authManager->itemTable . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
                ], $tableOptions);

        if ($this->isMSSQL()) {
            $this->execute("CREATE TRIGGER dbo.trigger_auth_item_child
            ON dbo.{$authManager->itemTable}
            INSTEAD OF DELETE, UPDATE
            AS
            DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
            DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
            BEGIN
            IF COLUMNS_UPDATED() > 0
                BEGIN
                    IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE auth_item_child NOCHECK CONSTRAINT FK__auth_item__child;
                        UPDATE auth_item_child SET child = @new_name WHERE child = @old_name;
                    END
                UPDATE auth_item
                SET name = (SELECT name FROM inserted),
                type = (SELECT type FROM inserted),
                description = (SELECT description FROM inserted),
                rule_name = (SELECT rule_name FROM inserted),
                data = (SELECT data FROM inserted),
                created_at = (SELECT created_at FROM inserted),
                updated_at = (SELECT updated_at FROM inserted)
                WHERE name IN (SELECT name FROM deleted)
                IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE auth_item_child CHECK CONSTRAINT FK__auth_item__child;
                    END
                END
                ELSE
                    BEGIN
                        DELETE FROM dbo.{$authManager->itemChildTable} WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                        DELETE FROM dbo.{$authManager->itemTable} WHERE name IN (SELECT name FROM deleted);
                    END
            END;");
        }
        
         $this->batchInsert($authManager->ruleTable, ['name', 'data', 'created_at', 'updated_at'], [           
            ['企业修改', 'O:45:"project\components\rule\CorporationUpdateRule":3:{s:4:"name";s:12:"企业修改";s:9:"createdAt";i:1536203524;s:9:"updatedAt";i:1536203524;}', '1521085145','1521085145'],
            ['企业删除', 'O:45:"project\components\rule\CorporationDeleteRule":3:{s:4:"name";s:12:"企业删除";s:9:"createdAt";i:1536203545;s:9:"updatedAt";i:1536203545;}', '1521085145','1521085145'],
        ]);

        //插入数据
        $this->batchInsert($authManager->itemTable, ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'], [
            ['/admin/*', '2', null, null, null, '1482820123', '1482820123'],
            
            ['/account/*', '2', null, null, null, '1482820123', '1482820123'],
            ['/account/change-password', '2', null, null, null, '1482820123', '1482820123'],
            ['/account/index', '2', null, null, null, '1482820123', '1482820123'],
            ['/account/thumb', '2', null, null, null, '1482820123', '1482820123'],
            
            ['/system/*', '2', null, null, null, '1482820123', '1482820123'],            
            ['/system/index', '2', null, null, null, '1482820123', '1482820123'],
            ['/system/smtp', '2', null, null, null, '1482820123', '1482820123'],
            ['/system/sms', '2', null, null, null, '1482820123', '1482820123'],
            ['/system/cdn', '2', null, null, null, '1482820123', '1482820123'],
            ['/system/captcha', '2', null, null, null, '1482820123', '1482820123'],
            ['/system/agreement', '2', null, null, null, '1482820123', '1482820123'],
            ['/system/crontab', '2', null, null, null, '1482820123', '1482820123'],
            
            ['/user/*', '2', null, null, null, '1482820123', '1482820123'],
            ['/user/user-list', '2', null, null, null, '1482820123', '1482820123'],
            
            ['/parameter/*', '2', null, null, null, '1482820123', '1482820123'],
            ['/parameter/parameter-list', '2', null, null, null, '1482820123', '1482820123'],
            
            ['/meal/*', '2', null, null, null, '1482820123', '1482820123'],
            ['/meal/meal-list', '2', null, null, null, '1482820123', '1482820123'],
            
            ['/industry/*', '2', null, null, null, '1482820123', '1482820123'],
            ['/industry/industry-list', '2', null, null, null, '1482820123', '1482820123'], 
            
            ['/corporation/*', '2', null, null, null, '1482820123', '1482820123'],
            ['/corporation/corporation-list', '2', null, null, null, '1482820123', '1482820123'],
            
            ['/allocate/*', '2', null, null, null, '1482820123', '1482820123'],
            ['/allocate/allocate-list', '2', null, null, null, '1482820123', '1482820123'],
            
          
            ['superadmin', '1', '超级管理员', null, null, '1482820123', '1482820123'],         
            ['member', '1', '注册会员', null, null, '1482820123', '1482820123'],
            ['frozen', '1', '账号冻结', null, null, '1482820123', '1482820123'],
            ['guest', '1', '游客', null, null, '1482820123', '1482820123'],
            
            ['pm', '1', '项目经理', null, null, '1482820123', '1482820123'],
            ['sa', '1', '解决方案', null, null, '1482820123', '1482820123'],
            ['ob', '1', '运营人员', null, null, '1482820123', '1482820123'],
            ['ob_data', '1', '数据运营', null, null, '1482820123', '1482820123'],
            ['bd', '1', '商务拓展', null, null, '1482820123', '1482820123'],
            
            ['系统设置', '2', '系统设置', null, null, '1482820123', '1482820123'],
            ['账号信息', '2', '账号信息', null, null, '1482820123', '1482820123'],
                       
//            ['业务中心', '2', '业务中心', null, null, '1482820123', '1482820123'],
            ['用户管理', '2', '用户管理', null, null, '1482820123', '1482820123'], 
            ['参数管理', '2', '参数管理', null, null, '1482820123', '1482820123'],
            ['套餐管理', '2', '套餐管理', null, null, '1482820123', '1482820123'],
            ['行业管理', '2', '行业管理', null, null, '1482820123', '1482820123'],
            ['企业管理', '2', '企业管理', null, null, '1482820123', '1482820123'],
            ['企业修改', '2', '企业修改', '企业修改', null, '1482820123', '1482820123'],
            ['企业删除', '2', '企业删除', '企业删除', null, '1482820123', '1482820123'],
            ['下拨管理', '2', '下拨管理', null, null, '1482820123', '1482820123'],
          
        ]);
        $this->batchInsert($authManager->itemChildTable, ['parent', 'child'], [
            ['ob_data', 'ob'],
            ['superadmin', 'pm'],
            
            ['superadmin', '/admin/*'],
            
            ['账号信息', '/account/*'],
            ['member', '账号信息'],
            ['frozen', '账号信息'],
            
            ['系统设置', '/system/*'], 
            ['superadmin', '系统设置'],
            
            ['用户管理', '/user/*'],
            ['pm', '用户管理'],
            
            ['参数管理', '/parameter/*'],
            ['pm', '参数管理'],
            ['ob_data', '参数管理'],
            
            ['套餐管理', '/meal/*'],
            ['pm', '套餐管理'],
            
            ['行业管理', '/industry/*'],
            ['pm', '行业管理'],
            ['ob_data', '行业管理'],
            
            ['企业管理', '/corporation/*'],
            ['企业管理', '企业修改'],
            ['企业管理', '企业删除'],
            ['pm', '企业管理'],
            ['sa', '企业管理'],
            ['ob', '企业管理'],
            ['bd', '企业管理'],
            
            ['下拨管理', '/allocate/*'],
            ['pm', '下拨管理'],
                               
        ]);
        $this->batchInsert($authManager->assignmentTable, ['item_name', 'user_id', 'created_at'], [
            ['superadmin', '1', '1482481221'],
            ['member', '1', '1482481221'],
            
            ['member', '2', '1482481221'],
            ['ob_data', '2', '1482481221'],
            
            ['member', '3', '1482481221'],
            ['ob', '3', '1482481221'],
            
            ['member', '4', '1482481221'],
            ['ob', '4', '1482481221'],
            
            ['member', '5', '1482481221'],
            ['sa', '5', '1482481221'],
            
            ['member', '6', '1482481221'],
            ['sa', '6', '1482481221'],
            
            ['member', '7', '1482481221'],
            ['bd', '7', '1482481221'],
            
            ['member', '8', '1482481221'],
            ['bd', '8', '1482481221'],
            
            ['member', '9', '1482481221'],
            ['bd', '9', '1482481221'],
            
            ['member', '10', '1482481221'],
            ['pm', '10', '1482481221'],
        ]);
     
    }

    /**
     * @inheritdoc
     */
    public function down() {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        if ($this->isMSSQL()) {
            $this->execute('DROP TRIGGER dbo.trigger_auth_item_child;');
        }

        $this->dropTable($authManager->assignmentTable);
        $this->dropTable($authManager->itemChildTable);
        $this->dropTable($authManager->itemTable);
        $this->dropTable($authManager->ruleTable);
    }

}
