<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use project\actions\IndexAction;
use project\models\ImportLog;
use project\models\ImportData;
use project\components\ExcelHelper;
use project\models\Field;
use project\models\Corporation;
use project\models\ActivityData;
use project\models\ActivityChange;
use project\models\ImportText;
use project\models\Group;
use project\models\UserGroup;


class ImportController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    $dataProvider = new ActiveDataProvider([
                        'query' => ImportLog::find()->andWhere(['or',['group_id'=> UserGroup::get_user_groupid(Yii::$app->user->identity->id)],['group_id'=>NULL]]),
                        'sort' => ['defaultOrder' => [                            
                            'id' => SORT_DESC,
                        ]],
                    ]);
                    return [
                        'dataProvider' => $dataProvider,
                    ];
                }
            ],
        ];
    }
    
    public function actionBind($id) {
        $model = ImportLog::findOne($id);
        
        if (Yii::$app->request->isPost&&$model->load(Yii::$app->request->post())) {
       
            $model->statistics_at= strtotime($model->statistics_at);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '操作成功。');
            } else {
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            if ($model !== null) {
                $model->statistics_at= $model->statistics_at>0?date('Y-m-d',$model->statistics_at):'';
                return $this->renderAjax('bind', [
                            'model' => $model,
                ]);
            } else {
                Yii::$app->session->setFlash('error', '项目不存在。');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
    }
    
    public function actionBindGroup($id) {
        $model = ImportLog::findOne($id);
        
        if (Yii::$app->request->isPost&&$model->load(Yii::$app->request->post())) {
       
            if ($model->save()) {
                Yii::$app->session->setFlash('success', '操作成功。');
            } else {
                Yii::$app->session->setFlash('error', '操作失败。');
            }
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            if ($model !== null) {
                $model->scenario='group';
                return $this->renderAjax('bind-group', [
                            'model' => $model,
                ]);
            } else {
                Yii::$app->session->setFlash('error', '内容不存在。');
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
    }
    
    public function actionInduce($id) {
        $model = ImportLog::findOne($id);
        if ($model !== null) {
            if($model->statistics_at&&$model->group_id){
                
                //字段检测
                $zd= ImportData::get_field_by_log($id);        
               
                $field_codenull = Field::get_field_notcode($zd); 
                
                $warning=[];
                if(!empty($field_codenull)){
                    $warning[]= '存在未匹配字段 [['.implode('，', array_keys($field_codenull)).']] ,请匹配相关字段';         
                }
                                             
                $field_exist= array_diff($zd, $field_codenull);//存在code的字段id
                $field_idcode = Field::get_code_by_id($field_exist);//项目id和code键值对
                
              
                $field_repeat=array_diff_assoc(array_filter($field_idcode), array_unique($field_idcode));
                if(!empty($field_repeat)){
                    $warning[]= '存在重复字段 [['.implode('，', $field_repeat).']] ,请检查相关字段';         
                }
                
                $code= ActivityData::get_code();
                unset($code['huawei_account']);
                unset($code['corporation_name']);
                $not_exist= array_diff(array_keys($code), $field_idcode);
//                $warning[]= 'code:'.json_encode($code,256);
//                $warning[]= 'idcode:'.json_encode($field_idcode,256);
//                $warning[]= 'exist:'.json_encode($not_exist,256);
                
                if(!empty($not_exist)){
                    $warning[]= '缺少字段 [['.implode('，', $not_exist).']] ,请检查相关字段';
                }
                if(!empty($warning)){
                    Yii::$app->session->setFlash('warning', $warning); 
                }
               
                //数据处理
               
                $corporation_datas=ImportData::get_data_indexcorporation($id,$field_exist);
                if(!empty($corporation_datas)){
                                     
                    $corporation_exist= ActivityData::get_corporationid_by_time($model->statistics_at);//活跃中已经存在企业ID
                    
                    $model_a=new ActivityData();
                    $model_a->loadDefaultValues();
                    $model_a->statistics_time=$model->statistics_at;
                    $model_a->group_id=$model->group_id;
                    
                    $num_add=$num_update=$num_delete=0;
                    //新增
                    $corporation_add= array_diff(array_keys($corporation_datas), $corporation_exist);
                    foreach($corporation_add as $add){
                        $model_add = clone $model_a;
                        $model_add->corporation_id=$add;
                        foreach($corporation_datas[$add] as $item=>$value){
                            if(isset($field_idcode[$item])){
                                $model_add->{$field_idcode[$item]}=$value;                              
                            }
                        }
                        if($model_add->save()){
                            $num_add++;
                        }else{
                            Yii::$app->session->setFlash('error', json_encode($model_add->getErrors(),256)); 
                        }                                             
                    }
                    
                    //更新
                    $corporation_update= array_intersect(array_keys($corporation_datas), $corporation_exist);
                    ActivityData::deleteAll(['statistics_time'=>$model->statistics_at,'corporation_id'=>$corporation_update]);//不需要项目
                    foreach($corporation_update as $update){
                        $model_update = clone $model_a;
                        $model_update->corporation_id=$update;
                        foreach($corporation_datas[$update] as $item=>$value){
                            if(isset($field_idcode[$item])){
                                $model_update->{$field_idcode[$item]}=$value;                                                              
                            }
                        }
                        if($model_update->save()){
                            $num_update++;
                        }                                             
                    }
                    
                    //未改变
                    $num_delete=count(array_diff($corporation_exist, array_keys($corporation_datas)));
                    
                    //或者删除
                    //ActivityData::deleteAll(['statistics_time'=>$model->statistics_at,'corporation_id'=>array_diff($corporation_exist, array_keys($corporation_datas))]);
                    
                    Yii::$app->session->setFlash('success', '新增 '.$num_add.'条，更新 '.$num_update.' 条，未改变 '.$num_delete.' 条数据');
                    
                    
                    //状态变化
                    ImportLog::updateAll(['stat'=> ImportLog::STAT_COVER], ['and',['statistics_at'=>$model->statistics_at,'group_id'=>$model->group_id],['not',['id'=>$model->id]]]);
                    $model->stat= ImportLog::STAT_INDUCE;
                    $model->save();
                    
                    //生成区间活跃数据
                    //删除旧数据
                    ActivityChange::deleteAll(['and',['<=','start_time',$model->statistics_at],['>=','end_time',$model->statistics_at],['group_id'=>$model->group_id]]);
                    ActivityChange::updateAll(['health'=> ActivityChange::HEALTH_WA],['and',['group_id'=>$model->group_id],['>','end_time',$model->statistics_at]]);//健康度
                    //前部分区间
                    $pre_time= ActivityData::get_pre_time($model->statistics_at,$model->group_id);
                    if($pre_time){                       
                        ActivityChange::induce_data($pre_time, $model->statistics_at,$model->group_id);
                                               
                    }
                    //后部分区间
                    $next_time= ActivityData::get_next_time($model->statistics_at,$model->group_id);
                    if($next_time){
                        ActivityChange::induce_data($model->statistics_at,$next_time,$model->group_id);
                        ActivityChange::updateAll(['act_trend'=> ActivityChange::TREND_WA],['start_time'=> $next_time,'group_id'=>$model->group_id]);                       
                    }
                                        
                    //设置活跃标志
                    ActivityChange::set_activity();
                    
                    //设置趋势
                    ActivityChange::set_trend();
                    
                    //设置健康度
                    ActivityChange::set_health();
                    
                    //清除缓存
                    Yii::$app->cache->delete('deviation');
//                    Yii::$app->cache->flush();
                                        
                }else{
                    Yii::$app->session->setFlash('error', '没有有效数据');
                }
                 
            }else{
                Yii::$app->session->setFlash('error', '请先设置统计日期或项目');                
            }
            
        } else {
            Yii::$app->session->setFlash('error', '内容不存在。');           
        }
        return $this->redirect(Yii::$app->request->referrer);
        
    }
    
    public function actionClean($id) {
        $model = ImportLog::findOne($id);
        if ($model !== null) {
            ActivityData::deleteAll(['statistics_time'=>$model->statistics_at,'group_id'=>$model->group_id]);
            ActivityChange::deleteAll(['and',['<=','start_time',$model->statistics_at],['>=','end_time',$model->statistics_at],['group_id'=>$model->group_id]]);
            ActivityChange::updateAll(['health'=> ActivityChange::HEALTH_WA],['and',['>','end_time',$model->statistics_at],['group_id'=>$model->group_id]]);//健康度
            
            $pre_time= ActivityData::get_pre_time($model->statistics_at,$model->group_id);
            $next_time= ActivityData::get_next_time($model->statistics_at,$model->group_id);
            if($pre_time&&$next_time){
                ActivityChange::induce_data($pre_time,$next_time,$model->group_id);          
            }
            if($next_time){
                ActivityChange::updateAll(['act_trend'=> ActivityChange::TREND_WA],['start_time'=> $next_time,'group_id'=>$model->group_id]);
            }
            $model->stat= ImportLog::STAT_START;
            $model->save();
            
            ActivityChange::set_activity();
            
            //设置趋势
            ActivityChange::set_trend();
            
            //设置健康度
            ActivityChange::set_health();
            
            //清除缓存
            Yii::$app->cache->delete('deviation');
//            Yii::$app->cache->flush();
                   
            
        } else {
            Yii::$app->session->setFlash('error', '项目不存在。');           
        }
        return $this->redirect(Yii::$app->request->referrer);
        
    }
    
    public function actionStart($id) {       
        $model = ImportLog::findOne($id);
        if ($model !== null&&$model->group_id) {
                          
            $transaction = Yii::$app->db->beginTransaction();
            try {               
                
                ImportData::deleteAll(['log_id'=>$model->id]);

                $datas = ImportText::find()->where(['log_id'=>$model->id])->select(['data'])->column();

                //项目处理
                if(isset($datas[0])){
                                      
                    $keys= array_filter(array_keys(json_decode($datas[0],true)));                                               
                    $exits_key= Field::get_name_id($keys);
                   
                    $field_huawei_account=Field::get_code_name('huawei_account', $keys);
                    $field_corporation_name=Field::get_code_name('corporation_name', $keys);
                    if(!$field_huawei_account){
                        Yii::$app->session->setFlash('error', '文件首行不存在或还未设置<<华为云账号>>字段');
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }else{
                    Yii::$app->session->setFlash('error', '没有有效数据');
                    return $this->redirect(Yii::$app->request->referrer);
                }

                $model_import_data=[];
                $corporations = Corporation::find()->where(['not',['huawei_account'=>NULL]])->select(['id','huawei_account'])->indexBy('huawei_account')->column();
                foreach ($datas as $key=>$data) {
//                    Yii::$app->session->setFlash('success', json_encode($datas,256));
//                    return true;

                    //数据处理
                    $data= array_filter(json_decode($data,true));//去除0值和空值


                    //$corporation= Corporation::findOne(['huawei_account'=>trim($data[$field_huawei_account])]);
                    if(!array_key_exists(trim($data[$field_huawei_account]), $corporations)){
                        //不存在
                        $corporation=new Corporation();
                        $corporation->loadDefaultValues();
                        $corporation->group_id=$model->group_id;
                        $corporation->huawei_account=trim($data[$field_huawei_account]);
                        $corporation->base_company_name=$field_corporation_name&&isset($data[$field_corporation_name])?trim($data[$field_corporation_name]):trim($data[$field_huawei_account]);
                        $corporation->save(false); 
                        $corporation_id=$corporation->id;
                    }else{
                        $corporation_id=$corporations[trim($data[$field_huawei_account])];
                    }


                    foreach($data as $k=>$v){
                        //每一项数据处理
                        if(isset($exits_key[$k])&&!in_array($k,[$field_huawei_account,$field_corporation_name])){
                            $v=str_replace(',', '', $v);//去除千分号
                            $v=$v?$v:0;
                            $model_import_data[]=['log_id'=>$model->id,'corporation_id'=>$corporation_id,'field_id'=>$exits_key[$k],'data'=>$v];
                        }

                    }


                }
                if(!empty($model_import_data)){
                    Yii::$app->db->createCommand()->batchInsert(ImportData::tableName(), ['log_id', 'corporation_id','field_id','data'], $model_import_data)->execute();
                }
                $model->stat= ImportLog::STAT_START;
                $model->save();
                $transaction->commit();
                Yii::$app->session->setFlash('success', '初始化成功。');
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
                Yii::$app->session->setFlash('error', '初始化失败。');
            }
                  
                  
        } else {
            Yii::$app->session->setFlash('error', '项目不存在。');
        }
       return $this->redirect(Yii::$app->request->referrer);
        
    }
    
    public function actionImport() {       
        //判断是否Ajax
          if (Yii::$app->request->isAjax) {

            if (empty($_FILES['files'])) {
                $postMaxSize = ini_get('post_max_size');
                $fileMaxSize = ini_get('upload_max_filesize');
                $displayMaxSize = $postMaxSize < $fileMaxSize ? $postMaxSize : $fileMaxSize;

                return json_encode(['error' => '没有文件上传,文件最大为' . $displayMaxSize], JSON_UNESCAPED_UNICODE);
                // or you can throw an exception
            }


            //目标文件夹，不存在则创建
            $targetFolder = '/data/import_data';
            $targetPath = Yii::getAlias('@webroot') . $targetFolder;

            if (!file_exists($targetPath)) {
                @mkdir($targetPath, 0777, true);
            }

            $files = $_FILES['files'];
            $filenames = $files['name'];

            $ext = explode('.', basename($filenames[0]));
            $f_name = md5(uniqid()) . "." . strtolower(array_pop($ext));
            $filename = $targetPath . DIRECTORY_SEPARATOR . $f_name;
            //文件存在则删除
            if (file_exists($filename)) {
                @unlink($filename);
            }
            
            $model= new ImportLog();
            $model->stat= ImportLog::STAT_UPLOAD;
            $model->uid=Yii::$app->user->identity->id;
            $model->created_at=time();
            $model->name=$filenames[0];
            $model->patch=$f_name;
            $group = Group::get_user_group(Yii::$app->user->identity->id);
            if(count($group)==1){
                $model->group_id= key($group);   
            }
            
            if (@move_uploaded_file($files['tmp_name'][0], $filename)&&$model->save()) {                

                $format = \PHPExcel_IOFactory::identify($filename);
                $objectreader = \PHPExcel_IOFactory::createReader($format);
                $objectPhpExcel = $objectreader->load($filename);

                $dataArray = $objectPhpExcel->getActiveSheet()->toArray(null, true, true, true);

                $datas = ExcelHelper::execute_array_label($dataArray);

                //项目处理
                if(isset($datas[0])){
                    $keys= array_filter(array_keys($datas[0]));                                               
                    $exits_key= Field::get_name_id($keys);

                    $diff=array_diff($keys, array_keys($exits_key));

                    if(!empty($diff)){
                        $field_modle=new Field();
                        $field_modle->loadDefaultValues();
                        foreach($diff as $field){
                            $_model = clone $field_modle;
                            $_model->name=$field;
                            $_model->save();
                        }
                        Yii::$app->session->setFlash('warning', '有新增字段，请设置相应代码');
                        $exits_key= Field::get_name_id($keys);
                    }
                    $import_text=[];
                    foreach ($datas as $key=>$data) {
                        $import_text[]=['log_id'=>$model->id,'data'=> json_encode($data,256)];
                    }
                    
                    if(!empty($import_text)){
                        if(Yii::$app->db->createCommand()->batchInsert(ImportText::tableName(), ['log_id', 'data'], $import_text)->execute()){
                            Yii::$app->session->setFlash('success', '导入成功。');
                        }else{
                            $model->delete();
                            Yii::$app->session->setFlash('error', '导入失败。');
                        }
                    }                    
                    return $this->redirect(Yii::$app->request->referrer);
                    
                }else{
                    Yii::$app->session->setFlash('error', '没有有效数据');
                    //throw new \Exception('没有有效数据');
                    $model->delete();
                    return $this->redirect(Yii::$app->request->referrer);
                }
              
            }else{
                Yii::$app->session->setFlash('error', '导入失败。');
            }           
        } else {
            Yii::$app->session->setFlash('error', '导入失败。');
        }
        return true;
        
    }
    
}
