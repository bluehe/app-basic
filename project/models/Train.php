<?php

namespace project\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%train}}".
 *
 * @property int $id
 * @property int $uid
 * @property int $created_at
 * @property int $updated_at
 * @property string $train_type
 * @property int $corporation_id
 * @property string $train_name
 * @property string $train_address
 * @property int $train_start
 * @property int $train_end
 * @property string $other_people
 * @property int $train_num
 * @property string $train_result
 * @property string $note
 * @property int $reply_uid
 * @property int $reply_at
 * @property int $stat
 *
 * @property User $u
 * @property User $replyU
 * @property Corporation $corporation
 */
class Train extends \yii\db\ActiveRecord
{
    public $sa;
    public $other;
    
    const STAT_CREATED = 1;
    const STAT_ORDER = 2;
    const STAT_REFUSE = 3; 
    const STAT_END = 4;
    const STAT_CANCEL = 5;
    
    const TYPE_CUSTOM_NEW=11;
    const TYPE_CUSTOM_VISIT=12;
    const TYPE_CUSTOM_TRAIN=13;
    const TYPE_CUSTOM_LIVING=14;
    const TYPE_CUSTOM_SOLVE=15;
    const TYPE_INSIDE_SHARE=21;
    const TYPE_INSIDE_THIRD=22;
    const TYPE_ACTIVE_YZ=31;
    const TYPE_ACTIVE_HW=32;
    const TYPE_ACTIVE_TH=33;
    const TYPE_OTHER=51;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%train}}';
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id','uid', 'created_at', 'updated_at', 'corporation_id', 'train_num', 'reply_uid', 'reply_at', 'train_stat'], 'integer'],
            [['group_id','uid','train_type','train_start','train_end'], 'required'],
            [['train_result'], 'required', 'on' => 'trainEnd'],
            [['sa'], 'requiredBySelfsa','skipOnEmpty' => false,'on' => ['trainStart','trainEnd']],
            [['other'], 'requiredBySelfother','skipOnEmpty' => false,'on' => ['trainStart','trainEnd']],
            [['train_name'],'requiredByCompanyid','skipOnEmpty' => false],
            [['train_result', 'train_note'], 'string'],
            [['train_type', 'train_name', 'train_address', 'other_people'], 'string', 'max' => 255],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
            [['reply_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['reply_uid' => 'id']],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => Group::className(), 'targetAttribute' => ['group_id' => 'id']],
        ];
    }
    
    public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            if($this->corporation_id){
                $this->train_name = $this->corporation->base_company_name;              
            }
            if($this->other_people){
                $this->other_people= str_replace(',', '，', $this->other_people);
            }
            return true;
        } else {
            return false;
        }
    }
    
    public function requiredByCompanyid($attribute, $params)
    {
        if (!$this->corporation_id&&!$this->$attribute){
                $this->addError($attribute,'名称不能为空');            
        }        
    }
    
    public function requiredBySelfsa($attribute, $params)
    {
        if (Yii::$app->user->identity->role== User::ROLE_SA&&(!$this->$attribute||!in_array(Yii::$app->user->identity->id,$this->$attribute))){
                $this->addError($attribute,'必须包含自己');            
        }        
    }
    
    public function requiredBySelfother($attribute, $params)
    {
        if (Yii::$app->user->identity->role!=User::ROLE_SA&&(!$this->$attribute||!in_array(Yii::$app->user->identity->id,$this->$attribute))){
                $this->addError($attribute,'必须包含自己');            
        }        
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '创建人',
            'group_id' => '项目',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'train_type' => '类型',
            'corporation_id' => '名称',
            'train_name' => '名称',
            'train_address' => '地址',
            'train_start' => '开始时间',
            'train_end' => '结束时间',
            'sa' => '解决方案人员',
            'other' => '其他人员',
            'other_people' => '其他人员',
            'train_num' => '参与人数',
            'train_result' => '总结',
            'train_note' => '备注',
            'reply_uid' => '回复人',
            'reply_at' => '回复时间',
            'train_stat' => '状态',
        ];
    }
    
     public static $List = [
        'train_stat' => [
            self::STAT_CREATED => "创建",
            self::STAT_ORDER => "预约",
            self::STAT_REFUSE => "拒绝",
            self::STAT_END => "完成",
            self::STAT_CANCEL => "取消"
        ],
        'train_type'=>[
            self::TYPE_CUSTOM_NEW=>'拓新',
            self::TYPE_CUSTOM_VISIT=>'拜访',
            self::TYPE_CUSTOM_TRAIN=>'培训',
            self::TYPE_CUSTOM_LIVING=>'促活',
            self::TYPE_CUSTOM_SOLVE=>'解决问题',
            self::TYPE_INSIDE_SHARE=>'解决方案分享',
            self::TYPE_INSIDE_THIRD=>'第三方赋能',
            self::TYPE_ACTIVE_YZ=>'云智金陵活动',
            self::TYPE_ACTIVE_HW=>'华为官方活动',
            self::TYPE_ACTIVE_TH=>'同行活动',
            self::TYPE_OTHER=>'其他',
        ],
        'type_stack'=>[
            self::TYPE_CUSTOM_NEW=>'custom',
            self::TYPE_CUSTOM_VISIT=>'custom',
            self::TYPE_CUSTOM_TRAIN=>'custom',
            self::TYPE_CUSTOM_LIVING=>'custom',
            self::TYPE_CUSTOM_SOLVE=>'custom',
            self::TYPE_INSIDE_SHARE=>'inside',
            self::TYPE_INSIDE_THIRD=>'inside',
            self::TYPE_ACTIVE_YZ=>'active',
            self::TYPE_ACTIVE_HW=>'active',
            self::TYPE_ACTIVE_TH=>'active',
            self::TYPE_OTHER=>'other',
        ]
        
    ];
     
    public function getTrainStat() {
        $stat = isset(self::$List['train_stat'][$this->train_stat]) ? self::$List['train_stat'][$this->train_stat] : null;
        return $stat;
    }
    
    public function getTrainType() {
        $type = isset(self::$List['train_type'][$this->train_type]) ? self::$List['train_type'][$this->train_type] : null;
        return $type;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getU()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReplyU()
    {
        return $this->hasOne(User::className(), ['id' => 'reply_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporation()
    {
        return $this->hasOne(Corporation::className(), ['id' => 'corporation_id']);
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
   public function getTrainUsers() 
   { 
       return $this->hasMany(TrainUser::className(), ['train_id' => 'id']); 
   } 
 
   /** 
    * @return \yii\db\ActiveQuery 
    */ 
    public function getUsers() 
    { 
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%train_user}}', ['train_id' => 'id']); 
    }
    
    public static function get_username($id,$role='sa') {
        //$user_id= VisitUser::find()->where(['train_id'=>$id])->select(['user_id'])->orderBy(['vuser_sort'=>SORT_ASC])->column();
        $data=[];
        if($role=='other'){            
            $users= TrainUser::find()->joinWith(['user'])->where(['and',['train_id'=>$id],['not',['role'=>'sa']]])->select(['username','nickname','user_id'])->orderBy(['tuser_sort'=>SORT_ASC])->asArray()->all();
        }else{
            $users=TrainUser::find()->joinWith(['user'])->where(['train_id'=>$id])->andFilterWhere(['role'=>$role])->select(['username','nickname','user_id'])->orderBy(['tuser_sort'=>SORT_ASC])->asArray()->all();
        }
        
        foreach($users as $user){
            $data[]=$user['nickname']?$user['nickname']:$user['username'];
        }
        if($role=='other'){
            $train= static::findOne($id);
            if($train->other_people){
                $data[]=$train->other_people;
            }
        }
       
        return implode('，', $data);
    }
    
    public static function get_train_num($start, $end,$stat='',$sum=1,$total=1,$group_id=null) {
        
        if(!$group_id){
            $group_id=UserGroup::get_user_groupid(Yii::$app->user->identity->id);
        }
        
        if($total==1){
             //所有
            $query = static::find()->andWhere(['group_id'=>$group_id])->andFilterWhere(['and',['>=', 'train_start', $start],['<=', 'train_end', $end]])->andFilterWhere(['train_stat'=>$stat])->orderBy(['MAX(train_start)'=>SORT_ASC]);
            if($sum==1){
                //天
                $query->groupBy(["FROM_UNIXTIME(train_start, '%Y-%m-%d')"])->select(['num'=>'count(*)','time'=>"FROM_UNIXTIME(train_start, '%Y-%m-%d')"])->indexBy(['time']);
            }elseif($sum==2){
                //周
                $query->groupBy(["FROM_UNIXTIME(train_start, '%Y-W%u')"])->select(['num'=>'count(*)','time'=>"FROM_UNIXTIME(train_start, '%Y-W%u')"])->indexBy(['time']);      
            }else{
                //月
                $query->groupBy(["FROM_UNIXTIME(train_start, '%Y-%m')"])->select(['num'=>'count(*)','time'=>"FROM_UNIXTIME(train_start, '%Y-%m')"])->indexBy(['time']);
            }
         
        }else{
            //个人
            $query = TrainUser::find()->joinWith(['train','user'])->andWhere([self::tableName().'.group_id'=>$group_id])->andFilterWhere(['and',['>=', 'train_start', $start],['<=', 'train_end', $end]])->andFilterWhere(['train_stat'=>$stat])->andFilterWhere(['role'=>'sa'])->orderBy(['MAX(train_start)'=>SORT_ASC]);
            if($sum==1){
                //天
                $query->groupBy(["FROM_UNIXTIME(train_start, '%Y-%m-%d')",'user_id'])->select(['num'=>'count(*)','time'=>"FROM_UNIXTIME(train_start, '%Y-%m-%d')",'train_id'=>'MAX(train_id)','user_id']);
            }elseif($sum==2){
                //周
                $query->groupBy(["FROM_UNIXTIME(train_start, '%Y-W%u')",'user_id'])->select(['num'=>'count(*)','time'=>"FROM_UNIXTIME(train_start, '%Y-W%u')",'train_id'=>'MAX(train_id)','user_id']);      
            }else{
                //月
                $query->groupBy(["FROM_UNIXTIME(train_start, '%Y-%m')",'user_id'])->select(['num'=>'count(*)','time'=>"FROM_UNIXTIME(train_start, '%Y-%m')",'train_id'=>'MAX(train_id)','user_id']);
            }
        }
        
        
        return $query->asArray()->all();
    }
    
    public static function get_train_type($start, $end,$stat='',$total=1,$group_id=null) {
        if(!$group_id){
            $group_id=UserGroup::get_user_groupid(Yii::$app->user->identity->id);
        }
        
        if($total==1){
             //所有
            $query = static::find()->andWhere(['group_id'=>$group_id])->andFilterWhere(['and',['>=', 'train_start', $start],['<=', 'train_end', $end]])->andFilterWhere(['train_stat'=>$stat])->orderBy(['MAX(train_type)'=>SORT_ASC])->groupBy(['train_type'])->select(['num'=>'count(*)','train_type'])->indexBy(['train_type']);
                     
        }else{
            //个人
            $query = TrainUser::find()->joinWith(['train','user'])->andWhere([self::tableName().'.group_id'=>$group_id])->andFilterWhere(['and',['>=', 'train_start', $start],['<=', 'train_end', $end]])->andFilterWhere(['train_stat'=>$stat])->andFilterWhere(['role'=>'sa'])->orderBy(['MAX(train_type)'=>SORT_ASC])->groupBy(['train_type','user_id'])->select(['num'=>'count(*)','train_type','train_id'=>'MAX(train_id)','user_id']);
            
        }
            
        return $query->asArray()->all();
    }
}
