<?php
namespace project\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $tel
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    
    const ROLE_PM = 'pm';
    const ROLE_SA = 'sa';
    const ROLE_OB = 'ob';
    const ROLE_OB_DATA = 'ob_data';
    const ROLE_BD = 'bd';
    
    public $group;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash'], 'required'],
            [['point','status', 'created_at', 'updated_at', 'last_login'], 'integer'],
            [['username', 'email', 'tel','user_color'], 'trim'],
            [['username', 'email', 'tel','user_color'], 'filter','filter'=>'strtolower'],
            [['username', 'email', 'tel', 'nickname'], 'unique', 'message' => '{attribute}已经存在'],            
            [['username', 'nickname'], 'string', 'max' => 16],
            [['username'], 'string', 'min' => 4],
            [['nickname'], 'string', 'min' => 2],
            [['password_hash', 'password_reset_token', 'email', 'tel', 'avatar'], 'string', 'max' => 255],
            [['point'], 'default', 'value' => 0],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['role','group'], 'safe'],
        ];
    }

    public function attributeLabels() {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'auth_key' => '密码',
            'password_hash' => '密码Hash',
            'password_reset_token' => '重置密码token',
            'nickname' => '昵称',
            'email' => 'Email',
            'tel' => '电话',
            'avatar' => '头像', 
            'gender' => '性别',
            'role' => '角色',
            'point' => '积分',
            'user_color' => '颜色',
            'group' => '项目',         
            'status' => '状态',
            'last_login' => '上次登录',
            'created_at' => '注册时间',
            'updated_at' => '更新时间',
        ];
    }

    public static $List = [
        'status' => [
            self::STATUS_ACTIVE => "正常",
            self::STATUS_DELETED => "删除"
        ],
        'role' => [
            self::ROLE_PM => "项目经理",
            self::ROLE_SA => "解决方案",
            self::ROLE_OB => "运营人员",
            self::ROLE_OB_DATA => "数据运营",
            self::ROLE_BD => "商务拓展"
        ],
    ];

    public function getStatus() {
        $status = isset(self::$List['status'][$this->status]) ? self::$List['status'][$this->status] : null;
        return $status;
    }
    
    public function getRole() {
        $role = isset(self::$List['role'][$this->role]) ? self::$List['role'][$this->role] : null;
        return $role;
    }
      
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAuths() {
        return $this->hasMany(UserAuth::className(), ['uid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogs() {
        return $this->hasMany(UserLog::className(), ['uid' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserPoints() {
        return $this->hasMany(UserPoint::className(), ['uid' => 'id']);
    }
 
    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }
    
    public static function findByLoginname($name)
    {
        return static::find()->where(['or',['username' => $name],['email' => $name],['tel' => $name]])->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public static function get_avatar($id) {
        $user = static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
        return $user && $user->avatar ? $user->avatar : '@web/image/user.png';
    }

    public static function get_nickname($id) {
        $user = static::findOne(['id' => $id]);
        return $user ? ($user->nickname ? $user->nickname : $user->username) : '';
    }

    public static function exist_nickname($nickname) {
        $user = static::findOne(['nickname' => $nickname]);
        return $user ? $user->id : false;
    }

    public static function get_day_total($a = 'created_at', $start = '', $end = '') {

        $query = static::find()->where(['status' => self::STATUS_ACTIVE])->andFilterWhere(['>=', $a, $start])->andFilterWhere(['<=', $a, $end]);
        return $query->groupBy(["FROM_UNIXTIME($a, '%Y-%m-%d')"])->select(['count(*)', "FROM_UNIXTIME($a,'%Y-%m-%d')"])->indexBy("FROM_UNIXTIME($a,'%Y-%m-%d')")->column();
    }
    
    public static function get_bd($stat=null,$id=null) {
        $data=[];
        $users = static::find()->where(['role'=>'bd'])->andFilterWhere(['status'=>$stat,'id'=>$id])->all();
        foreach($users as $user){
            $data[$user['id']]=$user['nickname']?$user['nickname']:$user['username'];
        }
        return $data;
       
    }
    
    public static function get_bd_color() {
        $data=[];
        $users = static::find()->where(['role'=>'bd'])->all();
        foreach($users as $user){
            $data[$user['id']]['name']=$user['nickname']?$user['nickname']:$user['username'];
            $data[$user['id']]['color']=$user['user_color'];
        }
        return $data;
       
    }
    
    public static function get_user_color() {
        $data=[];
        $users = static::find()->where(['status'=>self::STATUS_ACTIVE])->all();
        foreach($users as $user){
            $data[$user['id']]['name']=$user['nickname']?$user['nickname']:$user['username'];
            $data[$user['id']]['color']=$user['user_color'];
        }
        return $data;
       
    }
    
    public static function get_role($role='sa',$stat=null,$id=null) {
        $data=[];
        if($role=='other'){
            $users = static::find()->where(['not',['role'=>'sa']])->andFilterWhere(['status'=>$stat,'id'=>$id])->all();       
        }else{
            $users = static::find()->where(['role'=>$role])->andFilterWhere(['status'=>$stat,'id'=>$id])->all();
        }
        foreach($users as $user){
            $data[$user['id']]=$user['nickname']?$user['nickname']:$user['username'];
        }
        return $data;
    }
       
}
