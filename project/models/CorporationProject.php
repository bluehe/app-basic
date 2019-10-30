<?php

namespace project\models;

use Yii;
use project\components\CurlHelper;

/**
 * This is the model class for table "{{%corporation_project}}".
 *
 * @property int $id
 * @property int $corporation_id 企业ID
 * @property string $name 名称
 * @property string $description 项目描述
 * @property string $project_uuid 项目UUID
 * @property int $add_type 添加方式
 *
 * @property Corporation $corporation
 */
class CorporationProject extends \yii\db\ActiveRecord
{
    public $member;
    
    const TYPE_ADD = 1;
    const TYPE_SYSTEM = 2;
    const TYPE_CHECK = 3;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%corporation_project}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id','region','project_uuid','name'], 'required'],
            [['corporation_id', 'add_type'], 'integer'],
            [['name', 'description', 'project_uuid'], 'string', 'max' => 32],
            [['member'], 'safe'],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corporation_id' => '企业ID',
            'region' => '区域',
            'name' => '名称',
            'description' => '项目描述',
            'project_uuid' => '项目UUID',
            'add_type' => '添加方式',
            'member'=>'项目成员',
        ];
    }
    
    public static $List = [       
        'type'=>[
            self::TYPE_ADD=>'手动',
            self::TYPE_CHECK=>'检测',            
            self::TYPE_SYSTEM=>'系统',       
        ],
       
    ];
    
    public function getType() {
        $stat = isset(self::$List['type'][$this->add_type]) ? self::$List['type'][$this->add_type] : null;
        return $stat;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
    }
    
    public static function get_corporationproject_exist($id) {
        $region = CorporationMeal::get_region_by_id($id);
        return static::find()->where(['corporation_id'=>$id,'region'=>$region])->exists();
    }
    
    public static function project_delete($corporation_id){
        //不能删除项目，删除仓库、移除项目成员，删除账号
        $model = CorporationProject::findOne(['corporation_id'=>$corporation_id]);
        $stat=false;
        if ($model !== null) {
            
            //删除仓库
            $codehubs = CorporationCodehub::find()->where(['corporation_id'=>$corporation_id,'region'=>$model->region])->all();
            if($codehubs!==null){
                //存在仓库
                foreach ($codehubs as $codehub){
                    CorporationCodehub::codehub_delete($codehub->id)?Yii::info($corporation_id.'仓库删除成功'.$codehub->id, 'projectclean'):Yii::info($corporation_id.'仓库删除失败'.$codehub->id, 'projectclean');
                }
                
            }
            
            //移除项目成员
            $members=[];
            $token = CorporationAccount::get_token($corporation_id);
            $auth_member= CurlHelper::listMember($model, $token);
            if($auth_member['code']=='200'&&$auth_member['content']['status']=='success'){

                foreach ($auth_member['content']['result']['members'] as $member){
                    $members[$member['user_id']]=$member['role_id'];
                }               
            }           
            $delete_members= array_keys($members);
            if(count($delete_members)>1){
                foreach ($delete_members as $delete){
                    if($members[$delete]!=3){
                        $auth=CurlHelper::deleteMember($model, $delete, $token);                      
                    }
                }
                Yii::info($corporation_id.'移除成员', 'projectclean');               
            }
            
            //删除账号
            $accounts = CorporationAccount::find()->andWhere(['corporation_id'=> $corporation_id])->all();
            if(count($accounts)>1){
                foreach($accounts as $account){
                    $auth['code']='204';
                    if($account->add_type==CorporationAccount::TYPE_SYSTEM){
                        $auth=CurlHelper::deleteUser($account->user_id,$token);
                        if($auth['code']=='204'){
                            $account->delete();
                        }
                    }                   
                }
                Yii::info($corporation_id.'删除账号', 'projectclean');
            }
            $stat=true;
            
        }else{
            $stat=true;
        }        
        return $stat;
    }
}
