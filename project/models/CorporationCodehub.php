<?php

namespace project\models;

use Yii;
use project\components\CurlHelper;

/**
 * This is the model class for table "{{%corporation_codehub}}".
 *
 * @property int $id
 * @property int $corporation_id 企业ID
 * @property int $project_id 项目ID
 * @property string $repository_name 仓库名
 * @property string $project_uuid 项目UUID
 * @property string $repository_uuid 仓库UUID
 * @property string $https_url 仓库URL
 * @property int $status 仓库状态
 * @property int $add_type 添加方式
 * @property int $created_at 添加方式
 * @property int $updated_at 添加方式
 * @property string $username 创建时间
 * @property string $password 更新时间
 * @property int $ci 持续集成
 *
 * @property Corporation $corporation
 */
class CorporationCodehub extends \yii\db\ActiveRecord
{
    const TYPE_ADD = 1;
    const TYPE_SYSTEM = 2;
    const TYPE_CHECK = 3;
    
    const CI_NO = 1;
    const CI_YES = 2;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%corporation_codehub}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['corporation_id','project_id','https_url','ci','total_num','left_num'], 'required'],
            [[ 'username','password'], 'required','on'=>'update'],
            [['username', 'password'], 'trim'],
            [['corporation_id', 'project_id', 'status', 'add_type', 'created_at', 'updated_at', 'ci','total_num','left_num'], 'integer'],
            [['repository_name', 'project_uuid', 'repository_uuid', 'username', 'password'], 'string', 'max' => 32],
            [['https_url'], 'string', 'max' => 128],
            [['corporation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Corporation::className(), 'targetAttribute' => ['corporation_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => CorporationProject::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['total_num','left_num'], 'default', 'value' => 0],
            [['ci'], 'default', 'value' => self::CI_NO],
            [['status'], 'default', 'value' => 0],
        ];
    }
    
     public function beforeSave($insert) {
        // 注意，重载之后要调用父类同名函数
        if (parent::beforeSave($insert)) {
            if($this->password){
                $this->password = base64_encode($this->password);              
            }          
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->password=$this->password?base64_decode($this->password):'';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'corporation_id' => '企业ID',
            'project_id' => '项目ID',
            'repository_name' => '仓库名',
            'project_uuid' => '项目UUID',
            'repository_uuid' => '仓库UUID',
            'https_url' => '仓库URL',
            'status' => '仓库状态',
            'add_type' => '添加方式',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'username' => '用户名',
            'password' => '密码',
            'ci' => '持续集成',
            'total_num' => '定时次数',
            'left_num' => '剩余次数',
        ];
    }
    
    public static $List = [  
        'type'=>[
            self::TYPE_ADD=>'手动',
            self::TYPE_CHECK=>'检测',            
            self::TYPE_SYSTEM=>'系统',       
        ],
        'ci'=>[
            self::CI_YES=>'是',
            self::CI_NO=>'否',      
        ],
       
    ];
    
    public function getType() {
        $stat = isset(self::$List['type'][$this->add_type]) ? self::$List['type'][$this->add_type] : null;
        return $stat;
    }
    
    public function getCi() {
        $stat = isset(self::$List['ci'][$this->ci]) ? self::$List['ci'][$this->ci] : null;
        return $stat;
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
    public function getProject()
    {
        return $this->hasOne(CorporationProject::className(), ['id' => 'project_id']);
    }
    
    public static function get_codehub_exist($id) {
        return static::find()->where(['corporation_id'=>$id])->exists();
    }
    
    public static function get_last_codehubname($corporation_id) {
        $name= static::find()->where(['corporation_id'=>$corporation_id])->andWhere('repository_name REGEXP "^demo[0123456789]{1,}$"')->select(['repository_name'])->orderBy(['repository_name'=>SORT_DESC])->scalar();
        return $name?++$name:'demo01';       
    }
    
    public static function set_corporation_codehub_list($corporation_id) {
        $region = CorporationMeal::get_region_by_id($corporation_id);
        $project = CorporationProject::findOne(['corporation_id'=>$corporation_id,'region'=>$region]);
        $token = CorporationAccount::get_token($corporation_id);
        if($region&&$project&&$token){
            $auth= CurlHelper::listCodehub($project,$token);
            if($auth['code']=='200'){
                $codehubs = static::find()->where(['project_id'=>$project->id])->indexBy('repository_uuid')->asArray()->all();
                foreach($auth['content']['result']['repositories'] as $codehub){
                    $key= array_key_exists($codehub['repository_uuid'], $codehubs);
                    if($key){
                        if($codehubs[$codehub['repository_uuid']]['updated_at']!= strtotime($codehub['updated_at'])){
                            //更新修改时间
                            $model = static::findOne(['repository_uuid'=>$codehub['repository_uuid']]);
                            $model->updated_at=strtotime($codehub['updated_at']);
                            $model->save();
                        }
                        unset($codehubs[$codehub['repository_uuid']]);
                        continue;
                    }
                    
                    //增加仓库
                    $model = new CorporationCodehub();
                    $model->loadDefaultValues();
                    $model->corporation_id=$project->corporation_id;
                    $model->project_id=$project->id;
                    $model->repository_name=$codehub['repository_name'];
                    $model->project_uuid=$project->project_uuid;
                    $model->repository_uuid=$codehub['repository_uuid'];
                    $model->https_url=$codehub['https_url'];
                    $model->status=$codehub['status'];
                   
                    $model->add_type= static::TYPE_CHECK;
                    $model->created_at= strtotime($codehub['created_at']);
                    $model->updated_at=strtotime($codehub['updated_at']);
                    $model->ci= static::CI_NO;
                    $model->save();
                    return $model->getErrors();
                }
                
                //删除不存在仓库
                if($codehubs){
                    static::deleteAll(['project_id'=>$project->id,'repository_uuid'=> array_keys($codehubs)]);
                }
                return true;

            }else{
                return $auth;
            }
        }
        return false;
    }
    
    public static function get_codehub_sum($corporation_id,$ci=null) {
        $region = CorporationMeal::get_region_by_id($corporation_id);
        $sum= static::find()->alias('c')->joinWith(['project p'])->where(['c.corporation_id'=>$corporation_id])->andFilterWhere(['ci'=>$ci,'p.region'=>$region])->sum('total_num');
        return $sum?$sum:0;
    }
    
    public static function get_codehub_num($corporation_id,$ci=null) {
        $region = CorporationMeal::get_region_by_id($corporation_id);
        return static::find()->alias('c')->joinWith(['project p'])->where(['c.corporation_id'=>$corporation_id])->andFilterWhere(['ci'=>$ci,'p.region'=>$region])->count();
    }
    
    public static function codehub_exec($id) {
        $model=CorporationCodehub::findOne($id);
        
        $stat =false;
        if($model){
            $webroot= dirname(__DIR__).'/web';
            $targetFolder = '/data/git';
            $targetPath = $webroot . $targetFolder.'/'.$model->id;

            if (file_exists($targetPath)) { 
                if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
//                   echo $command='cd '.$targetPath.' && git pull && echo '.time().' > README.md && git add . && git commit -m "'.time().'" && git push';
                    $command="\"C:\Program Files\Git\bin\sh.exe\" ".$webroot ."/data/git.sh {$targetPath} ".time();
                }else{
                    $command="sudo ".$webroot ."/data/git.sh {$targetPath} ".time();
                } 
                exec($command.' >>codecommit.log 2>&1',$output,$status);
                if($status==0){
                    $stat=true;                      
                }
            }
        }

        return $stat;
        
    }
    
    public static function codehub_delete($id) {
        $model=CorporationCodehub::findOne($id);
        
        $stat =false;
        if($model){

            $status=0;
            if($model->username){
                $webroot= dirname(__DIR__).'/web';
                $targetFolder = '/data/git';
                $targetPath = $webroot . $targetFolder;

                if (!file_exists($targetPath)) {
                    @mkdir($targetPath, 0777, true);
                }
                if (file_exists($targetPath.'/'.$model->id)) {
                    if(strtoupper(substr(PHP_OS,0,3))==='WIN'){
                        $command='cd '.$targetPath.' && rd/s/q '.$model->id;
                    }else{
                        $command='cd '.$targetPath.' && sudo rm -rf '.$model->id;
                    } 
                    exec($command.' >>demo.log 2>&1',$output,$status);
                }
            }
            $auth['code']='200';
            if($model->add_type== CorporationCodehub::TYPE_SYSTEM){
                $project= CorporationProject::findOne(['id'=>$model->project_id]);
                $auth=CurlHelper::deleteCodehub($project,$model->repository_uuid,CorporationAccount::get_token($model->corporation_id));
            }
            if($status==0&&$auth['code']=='200'&&$model->delete()){
                $stat=true;
            }
        }

        return $stat;
        
    }
}
