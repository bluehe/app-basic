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


class ImportController extends Controller { 
    
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
//            $data= \project\models\CorporationBd::get_bd_by_time(1538228939);
//            var_dump($data);
//            exit;
                    $dataProvider = new ActiveDataProvider([
                        'query' => ImportLog::find(),
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
    
    public function actionInduce($id) {
        $model = ImportLog::findOne($id);
        if ($model !== null) {
            if($model->statistics_at){
                
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
                $not_exist= array_diff($code, $field_idcode);
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
                    ActivityData::deleteAll(['statistics_time'=>$model->statistics_at,'corporation_id'=>$corporation_update]);
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
                    ImportLog::updateAll(['stat'=> ImportLog::STAT_COVER], ['and',['statistics_at'=>$model->statistics_at],['not',['id'=>$model->id]]]);
                    $model->stat= ImportLog::STAT_INDUCE;
                    $model->save();
                    
                    //生成区间活跃数据
                    //删除旧数据
                    ActivityChange::deleteAll(['and',['<=','start_time',$model->statistics_at],['>=','end_time',$model->statistics_at]]);
                    //前部分区间
                    $pre_time= ActivityData::get_pre_time($model->statistics_at);
                    if($pre_time){                       
                        ActivityChange::induce_data($pre_time, $model->statistics_at);
                                               
                    }
                    //后部分区间
                    $next_time= ActivityData::get_next_time($model->statistics_at);
                    if($next_time){
                        ActivityChange::induce_data($model->statistics_at,$next_time);
                        ActivityChange::updateAll(['act_trend'=> ActivityChange::TREND_WA],['start_time'=> $next_time]);                       
                    }
                                        
                    //设置活跃标志
                    ActivityChange::set_activity();
                    
                    //设置趋势
                    ActivityChange::set_trend();
                    
                    //清除缓存
                    Yii::$app->cache->delete('deviation');
//                    Yii::$app->cache->flush();
                                        
                }else{
                    Yii::$app->session->setFlash('error', '没有有效数据');
                }
                 
            }else{
                Yii::$app->session->setFlash('error', '请先设置统计日期');                
            }
            
        } else {
            Yii::$app->session->setFlash('error', '项目不存在。');           
        }
        return $this->redirect(Yii::$app->request->referrer);
        
    }
    
    public function actionClean($id) {
        $model = ImportLog::findOne($id);
        if ($model !== null) {
            ActivityData::deleteAll(['statistics_time'=>$model->statistics_at]);
            ActivityChange::deleteAll(['and',['<=','start_time',$model->statistics_at],['>=','end_time',$model->statistics_at]]);
            
            $pre_time= ActivityData::get_pre_time($model->statistics_at);
            $next_time= ActivityData::get_next_time($model->statistics_at);
            if($pre_time&&$next_time){
                ActivityChange::induce_data($pre_time,$next_time);          
            }
            if($next_time){
                ActivityChange::updateAll(['act_trend'=> ActivityChange::TREND_WA],['start_time'=> $next_time]);
            }
            $model->stat= ImportLog::STAT_UPLOAD;
            $model->save();
            
            ActivityChange::set_activity();
            
            //设置趋势
            ActivityChange::set_trend();
            
            //清除缓存
            Yii::$app->cache->delete('deviation');
//            Yii::$app->cache->flush();
                   
            
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
            $model->created_at=time();
            $model->name=$filenames[0];
            $model->patch=$f_name;
            
            if (@move_uploaded_file($files['tmp_name'][0], $filename)&&$model->save()) {
               
                $import_data=new ImportData();
                $import_data->log_id=$model->id;
                //$datas = Excel::import($filename, ['headerTitle' => true, 'setFirstRecordAsKeys' => true,]);
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
                    $field_huawei_account=Field::get_code_name('huawei_account', $keys);
                    $field_corporation_name=Field::get_code_name('corporation_name', $keys);
                    if(!$field_huawei_account){
                        Yii::$app->session->setFlash('error', '文件首行不存在或还未设置<<华为云账号>>字段');
                        $model->delete();
                        return $this->redirect(Yii::$app->request->referrer);
                    }
                }else{
                    Yii::$app->session->setFlash('error', '没有有效数据');
                    $model->delete();
                    return $this->redirect(Yii::$app->request->referrer);
                }
                
                foreach ($datas as $key=>$data) {
//                    Yii::$app->session->setFlash('success', json_encode($datas,256));
//                    return true;
                    
                    //数据处理
                    $data= array_filter($data);//去除0值和空值
                                      
                    
                    $_model_import = clone $import_data;
                    $corporation= Corporation::findOne(['huawei_account'=>trim($data[$field_huawei_account])]);
                    if($corporation===null){
                        //不存在
                        $corporation=new Corporation();
                        $corporation->loadDefaultValues();
                        $corporation->huawei_account=trim($data[$field_huawei_account]);
                        $corporation->base_company_name=$field_corporation_name&&isset($data[$field_corporation_name])?trim($data[$field_corporation_name]):trim($data[$field_huawei_account]);
                        $corporation->save(false);                           
                    }
                    $_model_import->corporation_id=$corporation->id;

                    foreach($data as $k=>$v){
                        //每一项数据处理
                        if(isset($exits_key[$k])&&!in_array($k,[$field_huawei_account,$field_corporation_name])){
                            $_model_import_data = clone $_model_import;
                            $_model_import_data->field_id=$exits_key[$k];
                            $v=str_replace(',', '', $v);//去除千分号
                            $_model_import_data->data=$v?$v:0;
                            $_model_import_data->save();
                        }
                       
                    }
                                                         
            }
            Yii::$app->session->setFlash('success', '导入成功。');
                  
            }else{
                Yii::$app->session->setFlash('error', '导入失败。');
            }           
        } else {
            Yii::$app->session->setFlash('error', '导入失败。');
        }
        return true;
        
    }
    
}
