<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\Crontab;
use project\models\CorporationCodehub;
use project\models\CodehubExec;
use project\models\CorporationMeal;
use project\models\Corporation;

/**
 * 定时任务调度控制器
 * @author jlb
 */
class CrontabController extends Controller
{

    /**
     * 定时任务入口
     * @return int Exit code
     */
    public function actionIndex()
    {
    	$crontab = Crontab::findAll(['switch' => 1]);
    	$tasks = [];

    	foreach ($crontab as $task) {

    		// 第一次运行,先计算下次运行时间
    		if (!$task->next_rundate) {
    			$task->next_rundate = $task->getNextRunDate();
    			$task->save(false);
    			continue;
    		}

    		// 判断运行时间到了没
    		if ($task->next_rundate <= date('Y-m-d H:i:s')) {
                $tasks[] = $task;
    		}
    	}

        $this->executeTask($tasks);

        return ExitCode::OK;
    }
    
    /**
     * @param  array $tasks 任务列表
     * @author jlb
     */
    public function executeTask(array $tasks)
    {

        $pool = [];
        $startExectime = $this->getCurrentTime();

        foreach ($tasks as $task) {
            
            $pool[] = proc_open("php yii $task->route", [], $pipe);
        }

        // 回收子进程
        while (count($pool)) {
            foreach ($pool as $i => $result) {
                $etat = proc_get_status($result);
                if($etat['running'] == FALSE) {
                    proc_close($result);
                    unset($pool[$i]);
                    # 记录任务状态
                    $tasks[$i]->exectime     = round($this->getCurrentTime() - $startExectime, 2);
                    $tasks[$i]->last_rundate = date('Y-m-d H:i');
                    $tasks[$i]->next_rundate = $tasks[$i]->getNextRunDate();
                    $tasks[$i]->status       = 0;
                    // 任务出错
                    if ($etat['exitcode'] !== ExitCode::OK) {
                        $tasks[$i]->status = 1;
                    }   

                    $tasks[$i]->save(false);
                }
            }
        }
    }

    private function getCurrentTime ()  {  
        list ($msec, $sec) = explode(" ", microtime());  
        return (float)$msec + (float)$sec;  
    }
    
    public function actionGitExec()
    {

        $w = date('w'); //星期
        $H = date('H'); //小时
        $i = date('i'); //分钟
       
        //按分钟执行，一天为600次
        $cache=Yii::$app->cache;
        $gitexec_sum=$cache->get('gitexec_sum');
        if($gitexec_sum==null){
            $gitexec_sum= CorporationCodehub::find()->where(['>','total_num',0])->sum('total_num');
            if(!$gitexec_sum){
                $gitexec_sum=0;
            }
            
            $query = CorporationCodehub::find()->select(['SUM(total_num)'])->createCommand()->getRawSql();
            $dependency = new \yii\caching\DbDependency(['sql' => $query]);

            $cache->set('gitexec_sum', $gitexec_sum, null, $dependency);
        }

        $left_num = CorporationCodehub::find()->where(['>','left_num',0])->sum('left_num');
        if(!$left_num){
            $left_num=0;
        }
        
        $day_num= floor($gitexec_sum/5);//每天需要处理的执行数

        $left_day=$w>0&&$w<6?($left_num-$day_num*(5-$w)):$left_num;//当天剩余执行数
        $left_hour = $left_day>0?($H>=8&&$H<18?($left_day - floor($day_num/10*(17-$H))):$left_day):0;
        $r=$left_hour>0?mt_rand(0,floor((59-$i)/$left_hour)):0;
        if($left_hour<=0 || $r){
            Yii::info('无任务或者随机跳过,总次数：'.$gitexec_sum.'，当天剩余次数：'.$left_day.'，当前小时剩余次数：'.$left_hour.'，随机数：'.$r, 'gitexec');     
            return ExitCode::OK;
        }

        $codehubs = CorporationCodehub::find()->where(['>','left_num',0])->select(['id'])->column();
        if(count($codehubs)>0){
            $key = array_rand($codehubs);
            $id = $codehubs[$key];

            $stat = CorporationCodehub::codehub_exec($id);

            if($stat){
                $model = CorporationCodehub::findOne($id);
                $model->left_num--;
                $model->save();
                Yii::info('执行成功,ID:'.$id, 'gitexec');
            }else{
                Yii::error('执行失败', 'gitexec');
            }

            $exec = new CodehubExec();
            $exec->codehub_id=$id;
            $exec->updated_at=time();
            $exec->type= CodehubExec::TYPE_SYSTEM;
            $exec->stat = $stat?CodehubExec::STAT_YES:CodehubExec::STAT_NO;
            $exec->save();
        }else{
            Yii::warning('未找到执行任务', 'gitexec');
        }

        return ExitCode::OK;
                   
    }
    
    public function actionGitSet()
    {
        $num = CorporationCodehub::updateAll(['left_num'=>new \yii\db\Expression('total_num')], ['>','total_num',0]);
        return $num? ExitCode::OK:ExitCode::UNSPECIFIED_ERROR;;
        
    }
    
    public function actionProjectClean()
    {
        
        $ids = CorporationMeal::find()->groupBy(['corporation_id'])->having(['<=','MAX(end_time)',time()+86400*7])->select(['corporation_id'])->column();
        $corporation_ids = Corporation::find()->where(['id'=>$ids,'stat'=>[Corporation::STAT_ALLOCATE, Corporation::STAT_AGAIN]])->select(['id'])->column();
        if($corporation_ids){
            foreach ($corporation_ids as $corporation_id){
                \project\models\CorporationProject::project_delete($corporation_id)?'1':'2';
            }
        }
        return ExitCode::OK;
        
    }
   
}