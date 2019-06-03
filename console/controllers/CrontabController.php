<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use common\models\Crontab;
use project\models\CorporationCodehub;
use project\models\CodehubExec;

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

        $w = date('w');
        $H = date('H');
        $m = date('m');
        if ($w>0&&$w<6&&$H>=8&&$H<18){
            //按分钟执行，一天为660次
            $cache=Yii::$app->cache;
            $gitexec_sum=$cache->get('gitexec_sum');
            if($gitexec_sum==null){
                $gitexec_sum= CorporationCodehub::find()->sum('total_num');
                $cache->set('gitexec_sum', $gitexec_sum);
            }
                       
            $left_num = CorporationCodehub::find()->sum('left_num');
            
            $day_num= floor($gitexec_sum/5);//每天需要处理的执行数
            
            $left_day=$left_num-$day_num*(5-$w);//当天剩余执行数
            $left_hour = $left_day - floor($day_num/9*(17-$H));
            $r=mt_rand(0,floor((59-$m)/$left_hour));
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
                    Yii::info('执行成功', 'gitexec');
                }else{
                    Yii::info('执行失败', 'gitexec');
                }

                $exec = new CodehubExec();
                $exec->codehub_id=$id;
                $exec->updated_at=time();
                $exec->type= CodehubExec::TYPE_SYSTEM;
                $exec->stat = $stat?CodehubExec::STAT_YES:CodehubExec::STAT_NO;
                $exec->save();
            }else{
                Yii::info('未找到执行任务', 'gitexec');
            }

            return ExitCode::OK;
            
        }else{
            Yii::warning('不在任务时间内', 'gitexec');
            return ExitCode::OK;
        }
    }
   
}