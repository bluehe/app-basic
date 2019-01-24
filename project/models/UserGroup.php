<?php

namespace project\models;

use Yii;

/**
 * This is the model class for table "{{%group_user}}".
 *
 * @property int $group_id
 * @property int $user_id
 *
 * @property Group $group
 * @property User $user
 */
class UserGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'user_id'], 'required'],
            [['group_id', 'user_id'], 'integer'],
            [['group_id', 'user_id'], 'unique', 'targetAttribute' => ['group_id', 'user_id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'group_id' => '项目ID',
            'user_id' => '用户ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public static function get_user_groupid($id,$power=false) {
        //用户项目ID
        $query=static::find()->where(['user_id' => $id]);
        if($power){
            $auth = Yii::$app->authManager;
            $Role_admin=$auth->getRole('superadmin');
            if(!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)){
                //非超级管理员
                $group= self::get_user_groupid(Yii::$app->user->identity->id);
                $query->andWhere(['group_id'=>$group]);
            }
        }
        return $query->select(['group_id'])->distinct()->column();
    }
    
    public static function get_group_userid($id) {
        //项目用户ID
        return static::find()->where(['group_id' => $id])->select(['user_id'])->distinct()->column();
    }
    
    public static function get_nogroup_userid() {
        //无项目组用户ID
        $user= static::find()->select(['user_id'])->distinct()->column();
        return User::find()->where(['not',['id'=>$user]])->select(['id'])->column();
    }
}
