<?php

namespace project\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%group}}".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $title 简称
 * @property string $area 地域
 * @property string $address 地址
 * @property string $location 坐标
 */
class Group extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%group}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'location'], 'string', 'max' => 64],
            [['title'], 'string', 'max' => 16],
            [['area'], 'string', 'max' => 32],
            [['address'], 'string', 'max' => 128],
        ];
    }
    
    public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            //地址坐标
            if($this->address){
                $content = @file_get_contents("http://api.map.baidu.com/geocoder/v2/?address=".$this->address."&city=".$this->area."&output=json&ak=4yoFlMxYUv8jq6tpbai1cnvCXauAAxkG");
                $info = json_decode($content, true);
                if($info['status']==0&&$info['result']['precise']==1){
                    $this->location=($info['result']['location']['lng']+ mt_rand(-9, 9)*0.00001).','.($info['result']['location']['lat']+ mt_rand(-9, 9)*0.00001);                   
                }else{
                    $this->location=null;
                }
            }else{
                $this->location=null;
            }
           
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'title' => '简称',
            'area' => '地域',
            'address' => '地址',
            'location' => '坐标',
        ];
    }
    
    public static function get_groupid($power=false) {
        $query = static::find()->select(['id']);
        if($power){
            $auth = Yii::$app->authManager;
            $Role_admin=$auth->getRole('superadmin');
            if(!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)){
                //非超级管理员
                $group= UserGroup::get_user_groupid(Yii::$app->user->identity->id);
                $query->andWhere(['id'=>$group]);
            }
        }
        return $query->column();
        
    }
    
    public static function get_group($power=false) {
        $query = static::find();
        if($power){
            $auth = Yii::$app->authManager;
            $Role_admin=$auth->getRole('superadmin');
            if(!$auth->getAssignment($Role_admin->name, Yii::$app->user->identity->id)){
                //非超级管理员
                $group= UserGroup::get_user_groupid(Yii::$app->user->identity->id);
                $query->andWhere(['id'=>$group]);
            }
        }
        $groups=$query->orderBy(['id'=>SORT_ASC])->all();
        return ArrayHelper::map($groups, 'id', 'title');
    }
    
    public static function get_user_group($id) {
        //用户项目ID
        $usergroup=UserGroup::get_user_groupid($id);
        $groups=static::find()->andWhere(['id'=>$usergroup])->orderBy(['id'=>SORT_ASC])->all();
        
        return ArrayHelper::map($groups, 'id', 'title');
    }
}
