<?php

namespace project\models;

use Yii;
use project\components\CurlHelper;

/**
 * This is the model class for table "{{%corporation_account}}".
 *
 * @property int $id
 * @property int $corporation_id
 * @property string $account_name 账号名
 * @property string $user_name 用户名
 * @property string $password 密码
 * @property string $domain_id 租户ID
 * @property string $user_id 用户ID
 * @property int $is_admin
 * @property int $add_type 添加方式
 *
 * @property Corporation $corporation
 */
class CorporationAccount extends \yii\db\ActiveRecord
{
    
    const TYPE_ADD = 1;
    const TYPE_SYSTEM = 2;
    const TYPE_CHECK = 3;
     
    const ADMIN_YES = 1;
    const ADMIN_NO = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%corporation_account}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id','account_name'], 'required'],
            [[ 'password'], 'required','on'=>'create'],
            [['user_name'], 'unique','targetAttribute' => ['account_name', 'user_name'], 'message' => '{attribute}已经存在'],
            [['corporation_id', 'is_admin', 'add_type'], 'integer'],
            [['account_name', 'user_name', 'password', 'domain_id', 'user_id'], 'string', 'max' => 32],
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
            'account_name' => '账号名',
            'user_name' => '用户名',
            'password' => '密码',
            'domain_id' => '租户ID',
            'user_id' => '用户ID',
            'is_admin' => '管理权限',
            'add_type' => '添加方式',
        ];
    }
    
    public static $List = [       
        'type'=>[
            self::TYPE_ADD=>'手动',
            self::TYPE_CHECK=>'检测',            
            self::TYPE_SYSTEM=>'系统',       
        ],
        'admin'=>[
            self::ADMIN_YES=>'是',
            self::ADMIN_NO=>'否',     
        ],
    ];
    
    public function getType() {
        $stat = isset(self::$List['type'][$this->add_type]) ? self::$List['type'][$this->add_type] : null;
        return $stat;
    }
    
    public function getAdmin() {
        $stat = isset(self::$List['admin'][$this->is_admin]) ? self::$List['admin'][$this->is_admin] : null;
        return $stat;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
    }
    
    public static function get_corporationaccount_exist($id,$is_admin=null) {
        return static::find()->where(['corporation_id'=>$id])->andFilterWhere(['is_admin'=>$is_admin])->exists();
    }
    
    public static function get_corporation_account($id,$is_admin=null) {
        return static::find()->where(['corporation_id'=>$id])->andFilterWhere(['is_admin'=>$is_admin])->one();
    }
    
    public static function get_corporation_account_num($id,$is_admin=null) {
        return static::find()->where(['corporation_id'=>$id])->andFilterWhere(['is_admin'=>$is_admin])->count();
    }
    
    public static function get_last_username($corporation_id) {
        $name= static::find()->where(['corporation_id'=>$corporation_id])->andWhere(['like','user_name','user%',false])->select(['user_name'])->orderBy(['user_name'=>SORT_DESC])->scalar();
        return $name?++$name:'user01';       
    }
    
    public static function get_token($corporation_id,$is_admin) {
        $account= static::find()->where(['corporation_id'=>$corporation_id])->andFilterWhere(['is_admin'=>$is_admin])->one();  
        if($account){
            $cache=Yii::$app->cache;
            $token = $cache->get('accountToken_'.$account->id);
            if($token===false){
                $auth = CurlHelper::authToken($account);
                if($auth['code']=='201'){
                    $cache->set('accountToken_'.$account->id,$auth['token'], strtotime($auth['content']['token']['expires_at'])-time());
                }else{
                    return false;
                }
                
            }
            return $token;
        }else{
            return false;
        }
    }
    
    public static function set_corporation_account_list($corporation_id) {
        $account = self::get_corporation_account($corporation_id, self::ADMIN_YES);
        $token = self::get_token($corporation_id, self::ADMIN_YES);
        if($account&&$token){
            $auth= CurlHelper::listUser($token);
            if($auth['code']=='200'){
                $accounts = static::find()->where(['corporation_id'=>$corporation_id])->select(['user_name','id'])->indexBy('id')->column();
                foreach($auth['content']['users'] as $user){
                    $key=array_search($user['name'], $accounts);
                    if($key){
                        unset($accounts[$key]);
                        continue;
                    }
                    
                    $model = new CorporationAccount();
                    $model->corporation_id=$corporation_id;
                    $model->account_name=$account->account_name;
                    $model->user_name=$user['name'];
                    $model->is_admin= CorporationAccount::ADMIN_NO;
                    $model->add_type= CorporationAccount::TYPE_CHECK;
                    $model->domain_id=$user['domain_id'];
                    $model->user_id=$user['id'];
                    $model->save();
                }
                
                //删除不存在用户
                if($accounts){
                    static::deleteAll(['corporation_id'=>$corporation_id,'user_name'=>$accounts]);
                }
                return true;

            }else{
                return $auth;
            }
        }
        return false;
    }
    
}
