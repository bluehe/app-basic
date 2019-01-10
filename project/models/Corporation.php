<?php

namespace project\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "corporation".
 *
 * @property int $id ID
 * @property string $base_company_name 公司名称
 * @property int $base_company_scale 企业规模
 * @property string $base_registered_capital 注册资金
 * @property int $base_registered_time 注册日期
 * @property string $base_main_business 主营业务
 * @property string $base_last_income 近一年营业收入
 * @property int $stat 状态
 * @property int $intent_set 意向套餐
 * @property string $huawei_account 华为云账号
 * @property string $note 备注
 * @property int $contact_park 所属园区
 * @property string $contact_address 实际地址
 * @property string $contact_location 坐标
 * @property string $contact_business_name 商业联系人
 * @property string $contact_business_job 商业联系人职务
 * @property string $contact_business_tel 商业联系人电话
 * @property string $contact_technology_name 技术联系人
 * @property string $contact_technology_job 技术联系人职务
 * @property string $contact_technology_tel 技术联系人电话
 * @property int $develop_scale 研发规模
 * @property string $develop_pattern 开发模式
 * @property string $develop_scenario 开发场景
 * @property string $develop_science 开发环境
 * @property string $develop_language 开发语言
 * @property string $develop_IDE 开发IDE
 * @property string $develop_current_situation 研发工具现状
 * @property string $develop_weakness 研发痛点
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 * @property CorporationBd[] $corporationBds
 * @property CorporationIndustry[] $corporationIndustries
 * @property Industry[] $industries
 */
class Corporation extends \yii\db\ActiveRecord
{
    
    public $base_industry;
    
    const STAT_CREATED = 1;
    const STAT_FOLLOW = 2;
//    const STAT_REGISTER = 4;
    const STAT_APPLY = 5;
    const STAT_CHECK = 6;
    const STAT_ALLOCATE = 7;
    const STAT_AGAIN = 8;
    const STAT_REFUSE = 0;
    const STAT_OVERDUE = -10;    
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%corporation}}';
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
            [['base_company_name', 'huawei_account'], 'trim'],
            [['base_company_name','huawei_account'], 'unique', 'message' => '{attribute}已经存在'],
            [['base_company_name','stat'], 'required'],
            [['base_industry'], 'required','on'=>'industry'],
            [['huawei_account','intent_set','intent_number'],'requiredByStat_r','skipOnEmpty' => false],
            [['huawei_account'],'requiredByStat_a','skipOnEmpty' => false],
            [['base_bd'],'requiredByStat_b','skipOnEmpty' => false],
            [['base_registered_time','base_industry'], 'safe'],
            [['base_bd','base_company_scale', 'stat', 'intent_set','intent_number', 'contact_park', 'develop_scale', 'created_at', 'updated_at'], 'integer'],
            [['base_registered_capital', 'base_last_income','intent_amount'], 'number'],
            [['base_main_business', 'note', 'develop_current_situation', 'develop_weakness'], 'string'],
            [['base_company_name', 'huawei_account', 'contact_business_tel', 'contact_technology_tel'], 'string', 'max' => 32],
            [['contact_address'], 'string', 'max' => 128],
            [['contact_location'], 'string', 'max' => 64],
            [['contact_business_name', 'contact_business_job', 'contact_technology_name', 'contact_technology_job'], 'string', 'max' => 16],
            [['develop_pattern', 'develop_scenario', 'develop_science', 'develop_language', 'develop_IDE'], 'safe'],
            [['base_bd'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['base_bd' => 'id']],
            [['intent_set'], 'exist', 'skipOnError' => true, 'targetClass' => Meal::className(), 'targetAttribute' => ['intent_set' => 'id']],
            [['stat'], 'default', 'value' => self::STAT_CREATED],
        ];
    }
    
    public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            //地址坐标
            if($this->contact_address){
                $content = @file_get_contents("http://api.map.baidu.com/geocoder/v2/?address=".$this->contact_address."&city=南京市&output=json&ak=4yoFlMxYUv8jq6tpbai1cnvCXauAAxkG");
                $info = json_decode($content, true);
                if($info['status']==0&&$info['result']['precise']==1){
                    $this->contact_location=($info['result']['location']['lng']+ mt_rand(-9, 9)*0.00001).','.($info['result']['location']['lat']+ mt_rand(-9, 9)*0.00001);                   
                }else{
                    $this->contact_location=null;
                }
            }else{
                $this->contact_location=null;
            }
            if(!$this->base_bd){
                $this->base_bd=null;
            }
            
            //意向金额
            if($this->stat==self::STAT_APPLY&&$this->intent_set&&$this->intent_number){
                 $this->intent_amount = $this->intent_number*Meal::get_meal_amount($this->intent_set);
            }
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        if ($insert) {
    
            //BD
            $bdModel = new CorporationBd();
            $bdModel->corporation_id=$this->id;
            $bdModel->bd_id=$this->base_bd;
            $bdModel->start_time=strtotime(date('Y-m-d',time()));
            $bdModel->save();
            
            //状态
            $statModel=new CorporationStat();
            $statModel->corporation_id=$this->id;
            $statModel->stat=$this->stat;
            $statModel->user_id=Yii::$app->user->identity->id;
            $statModel->created_at=time();
            $statModel->save();
        } else {
            if(array_key_exists('base_bd', $changedAttributes)&&$changedAttributes['base_bd']!=$this->base_bd){
                //BD变动，修改历史记录表,删除权限缓存
                $corporation_bd= CorporationBd::findOne(['corporation_id'=>$this->id,'start_time'=>strtotime(date('Y-m-d',time()))]);
                if($corporation_bd==null){
                    CorporationBd::updateAll(['end_time'=>strtotime(date('Y-m-d',time()))], ['corporation_id'=>$this->id,'bd_id'=>$changedAttributes['base_bd']]);
                    $bdModel = new CorporationBd();
                    $bdModel->corporation_id=$this->id;
                    $bdModel->bd_id=$this->base_bd;
                    $bdModel->start_time=strtotime(date('Y-m-d',time()));
                    $bdModel->save();
                }else{
                    //同一天改动BD
                    $corporation_bd->bd_id=$this->base_bd;
                    $corporation_bd->save();
                }
                
                $cache = Yii::$app->cache;
                $corporation_update = $cache->get('corporation_update');
                if ($corporation_update !== false&&isset($corporation_update[$this->id])) {
                    unset($corporation_update[$this->id]);                
                    $cache->set('corporation_update', $corporation_update);
                }
                $corporation_delete = $cache->get('corporation_delete');
                if ($corporation_delete !== false&&isset($corporation_delete[$this->id])) {
                    unset($corporation_delete[$this->id]);                
                    $cache->set('corporation_delete', $corporation_delete);
                }
            }
            
            if(array_key_exists('stat', $changedAttributes)&&$changedAttributes['stat']!=$this->stat){
                //状态变动，修改历史记录表
                $statModel=new CorporationStat();
                $statModel->corporation_id=$this->id;
                $statModel->stat=$this->stat;
                $statModel->user_id=Yii::$app->user->identity->id;
                $statModel->created_at=in_array($this->stat,[self::STAT_ALLOCATE, self::STAT_AGAIN])? CorporationMeal::get_created_time($this->id):time();
                $statModel->save();               
            }
                      
        }
        
        parent::afterSave($insert, $changedAttributes);
    }
    
    public function requiredByStat_r($attribute, $params)
    {
        if ($this->stat==self::STAT_APPLY&&!$this->$attribute){
                $this->addError($attribute,'不能为空。');            
        }
    }
    
    public function requiredByStat_a($attribute, $params)
    {
        if ($this->stat==self::STAT_ALLOCATE&&!$this->$attribute){
                $this->addError($attribute,'华为云账号不能为空。');            
        }
    }
    
    public function requiredByStat_b($attribute, $params)
    {
        if ($this->stat!=self::STAT_CREATED&&!$this->$attribute){
                $this->addError($attribute,'客户经理不能为空。');            
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'base_company_name' => '公司名称',
            'base_bd' => '客户经理',
            'base_industry'=>'行业',
            'base_company_scale' => '企业规模(人)',
            'base_registered_capital' => '注册资金(万元)',
            'base_registered_time' => '注册日期',
            'base_main_business' => '主营业务',
            'base_last_income' => '近一年营收(万元)',
            'stat' => '状态',
            'intent_set' => '意向套餐',
            'intent_number' => '意向数量',
            'intent_amount' => '意向金额',
            'huawei_account' => '华为云账号',
            'note' => '备注',
            'contact_park' => '所属园区',
            'contact_address' => '实际地址',
            'contact_location' => '坐标',
            'contact_business_name' => '商业联系人',
            'contact_business_job' => '商业联系人职务',
            'contact_business_tel' => '商业联系人电话',
            'contact_technology_name' => '技术联系人',
            'contact_technology_job' => '技术联系人职务',
            'contact_technology_tel' => '技术联系人电话',
            'develop_scale' => '研发规模(人)',
            'develop_pattern' => '开发模式',
            'develop_scenario' => '开发场景',
            'develop_science' => '开发环境',
            'develop_language' => '开发语言',
            'develop_IDE' => '开发IDE',
            'develop_current_situation' => '研发工具现状',
            'develop_weakness' => '研发痛点',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
    
    public static $List = [       
        'stat'=>[
            self::STAT_CREATED=>'新创建',
            self::STAT_FOLLOW=>'跟进中',            
//            self::STAT_REGISTER=>'已注册',
            self::STAT_APPLY=>'已申请',            
            self::STAT_CHECK=>'已审核',
            self::STAT_ALLOCATE=>'已下拨',
            self::STAT_AGAIN=>'已续拨',
            self::STAT_REFUSE=>'无意愿',
            self::STAT_OVERDUE=>'已过期'
        ],
        'column'=>[
//            'id' => 'ID',
//            'base_company_name' => '公司名称',
//            'base_bd' => '客户经理',
            'huawei_account' => '华为云账号',
            'base_industry'=>'行业',
            'contact_park' => '所属园区',
            'contact_address' => '实际地址',
            'contact_location' => '坐标',
            'intent_set' => '意向套餐',
            'intent_number' => '意向数量',
            'intent_amount' => '意向金额',
            'base_company_scale' => '企业规模(人)',
            'base_registered_capital' => '注册资金',
            'base_registered_time' => '注册日期',
            'base_main_business' => '主营业务',
            'base_last_income' => '近一年营收',
//            'stat' => '状态',
//            'note' => '备注',           
            'contact_business_name' => '商业联系人',
            'contact_business_job' => '商业联系人职务',
            'contact_business_tel' => '商业联系人电话',
            'contact_technology_name' => '技术联系人',
            'contact_technology_job' => '技术联系人职务',
            'contact_technology_tel' => '技术联系人电话',
            'develop_scale' => '研发规模(人)',
            'develop_pattern' => '开发模式',
            'develop_scenario' => '开发场景',
            'develop_science' => '开发环境',
            'develop_language' => '开发语言',
            'develop_IDE' => '开发IDE',
            'develop_current_situation' => '研发工具现状',
            'develop_weakness' => '研发痛点',
//            'created_at' => '创建时间',
//            'updated_at' => '更新时间',
        ]
       
    ];
    
    public function getStat() {
        $stat = isset(self::$List['stat'][$this->stat]) ? self::$List['stat'][$this->stat] : null;
        return $stat;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseBd()
    {
        return $this->hasOne(User::className(), ['id' => 'base_bd']);
    }
    
    /**
    * @return \yii\db\ActiveQuery
    */
    public function getIntentSet()
    {
        return $this->hasOne(Meal::className(), ['id' => 'intent_set']);
    }
    
//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getActivityChanges()
//    {
//       return $this->hasMany(ActivityChange::className(), ['corporation_id' => 'id']);
//    }
//    
//   /**
//    * @return \yii\db\ActiveQuery
//    */
//   public function getActivityDatas()
//   {
//       return $this->hasMany(ActivityData::className(), ['corporation_id' => 'id']);
//   }
//   
//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getCorporationIndustries()
//    {
//        return $this->hasMany(CorporationIndustry::className(), ['corporation_id' => 'id']);
//    }
//    
//    /**
//    * @return \yii\db\ActiveQuery
//    */
//    public function getUploadDatas()
//    {
//        return $this->hasMany(UploadData::className(), ['corporation_id' => 'id']);
//    }
    
    public static function get_industry($id) {
        $industry_id= CorporationIndustry::find()->where(['corporation_id'=>$id])->select(['industry_id'])->column();

        $industry_name= $industry_id?Industry::getIndustryName($industry_id):[];
       
        return implode('，', $industry_name);
    }

    
//    public static function get_existindustry() {
//        $ids = CorporationIndustry::find()->select(['industry_id'])->distinct()->column();
//        $industry= Industry::find()->where(['id'=>$ids])->all();
//        return ArrayHelper::map($industry, 'id', 'name');
//    }
//    
    public static function get_existbd() {
        return static::find()->select(['base_bd'])->distinct()->column();
    }
    
    //得到ID-name 键值数组
    public static function get_corporation_id() {
        $corporation = static::find()->where(['not',['base_company_name'=>'']])->orderBy(['id'=>SORT_ASC])->all();
        return ArrayHelper::map($corporation, 'id', 'base_company_name');
    }
       
//    public static function get_location() {
//        $data=[];
//        $locations= static::find()->where(['not',['contact_location'=>NULL]])->select(['base_company_name','contact_location','contact_address','id'])->all();
//        $t= ActivityChange::find()->orderBy(['end_time'=>SORT_DESC])->select(['end_time'])->scalar();
//        $corporation_id= ActivityChange::find()->where(['end_time'=>$t,'is_act'=> ActivityChange::ACT_Y])->select(['corporation_id'])->column();
//        foreach($locations as $location){
//            $l= explode(',', $location['contact_location']);
//            $data[]=['name'=>$location['base_company_name'],'address'=>$location['contact_address'],'lng'=>$l[0],'lat'=>$l[1],'activity'=> in_array($location['id'], $corporation_id)];
//        }
//        return $data;
//       
//    }
//    
    public static function get_stat_list($stat=self::STAT_FOLLOW) {
        $data=[];
        foreach(self::$List['stat'] as $k=>$v){
            if($k>=$stat){
                $data[$k]=$v;
            }
        }
        return $data;
        
    }
   

    
    public static function get_capital_total() {
        return static::find()->andWhere(['>','base_registered_capital',0])->select(["(CASE WHEN base_registered_capital>0 AND base_registered_capital<=500 THEN '0-500万' WHEN base_registered_capital>500 AND base_registered_capital<=3000 THEN '500-3000万' WHEN base_registered_capital>3000 AND base_registered_capital<=5000 THEN '3000-5000万' WHEN base_registered_capital>5000 THEN '5000万以上' ELSE '未设置' END) as title,count(*) as num"])->groupBy(['title'])->orderBy(['MAX(base_registered_capital)'=>SORT_ASC])->asArray()->all();
    }
    
     public static function get_scale_total() {
        return static::find()->andWhere(['>','develop_scale',0])->select(["(CASE WHEN develop_scale>0 AND develop_scale<=10 THEN '1-10人' WHEN develop_scale>10 AND develop_scale<=20 THEN '11-20人' WHEN develop_scale>20 AND develop_scale<=40 THEN '21-40人' WHEN develop_scale>40 THEN '40人以上' ELSE '未设置' END) as title,count(*) as num"])->groupBy(['title'])->orderBy(['MAX(develop_scale)'=>SORT_ASC])->asArray()->all();
    }
}
