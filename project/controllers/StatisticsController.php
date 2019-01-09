<?php

namespace project\controllers;

use Yii;
use yii\web\Controller;
use project\models\User;
use project\models\UserLog;
use project\models\ActivityChange;
use project\models\Corporation;
use project\models\CorporationMeal;
use project\models\CorporationIndustry;
use project\models\Industry;
use project\models\Train;
use project\models\ClouldSubsidy;

class StatisticsController extends Controller {

    public function actionUser() {

        $start = strtotime('-30 days');
        $end = strtotime('today') + 86399;

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]) + 86399 : $end;
        }
        $series = [];

        //用户趋势
        $day_signup = User::get_day_total('created_at', $start, $end);
        $day_log = UserLog::get_day_total('created_at', $start, $end);
      

        $data_signup = [];
        $data_log = [];
       

        for ($i = $start; $i < $end; $i = $i + 86400) {
            $j = date('Y-m-d', $i);
            $data_signup[] = ['name' => $j, 'y' => isset($day_signup[$j]) ? (int) $day_signup[$j] : 0];
            $data_log[] = ['name' => $j, 'y' => isset($day_log[$j]) ? (int) $day_log[$j] : 0];
           
        }
        $series['day'][] = ['type' => 'line', 'name' => '注册', 'data' => $data_signup];
        $series['day'][] = ['type' => 'line', 'name' => '登录', 'data' => $data_log];
      


        return $this->render('user', ['series' => $series, 'start' => $start, 'end' => $end]);
    }
    
    public function actionCorporation() {
         
        //行业
        $series['industry'] = [];
        $drilldown['industry']=[];
        
        $industry_num= CorporationIndustry::get_industry_total();
        $industrys= Industry::find()->where(['id'=> array_keys($industry_num)])->indexBy('id')->all();
        $parent=$sum=[];
        
        foreach($industry_num as $key=>$num){
            if($industrys[$key]['parent_id']){
                $parent[$industrys[$key]['parent_id']][]=[$industrys[$key]['name'],(int)$num];
                $sum[$industrys[$key]['parent_id']]=isset($sum[$industrys[$key]['parent_id']])?$sum[$industrys[$key]['parent_id']]+$num:(int)$num;
            }else{
                //$parent[$industrys[$key]['id']][]=[$industrys[$key]['name'],(int)$num];
                $sum[$industrys[$key]['id']]=isset($sum[$industrys[$key]['id']])?$sum[$industrys[$key]['id']]+$num:(int)$num;
            }
        }
        
        $parents= Industry::find()->where(['id'=> array_keys($sum)])->indexBy('id')->all();
        arsort($sum);
        $serie_data=$drilldown_data=[];
        foreach($sum as $k=>$s){
            if(isset($parent[$k])){
                $serie_data[]=['name'=>$parents[$k]['name'],'y'=>$s,'drilldown'=>$parents[$k]['name']];
                $drilldown_data[]=['name'=>$parents[$k]['name'],'id'=>$parents[$k]['name'],'data'=>$parent[$k]];
            }else{
                $serie_data[]=['name'=>$parents[$k]['name'],'y'=>$s,'drilldown'=>false];
            }
        }
        
        $series['industry'][]=['name'=>'一级分类','colorByPoint'=>true,'data'=>$serie_data];
        $drilldown['industry']=['series'=>$drilldown_data];
        
        //注册资金
        $series['capital']=[];       
        $data_capital=[];     
        $capitals= Corporation::get_capital_total();
        foreach($capitals as $capital){
            $data_capital[] = ['name' =>  $capital['title'], 'y' => (int) $capital['num']];
        }
        $series['capital'][] = ['type' => 'pie','innerSize'=>'50%', 'name' => '数量', 'data' => $data_capital];
        
        //研发规模
        $series['scale']=[];       
        $data_scale=[];     
        $scales= Corporation::get_scale_total();
        foreach($scales as $scale){
            $data_scale[] = ['name' =>  $scale['title'], 'y' => (int) $scale['num']];
        }
        $series['scale'][] = ['type' => 'pie','innerSize'=>'50%', 'name' => '数量', 'data' => $data_scale];
        
        
        //下拨额
        $series['amount'] = [];
        $end = strtotime('today');
        $start = strtotime('-1 year',$end);
        $sum=Yii::$app->request->get('sum',1);//1-天；2-周；3-月

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]) : $end;
        }

        $allocate_total= CorporationMeal::get_amount_total($start,$end,$sum);
        $base_amount= (float)CorporationMeal::get_amount_base($start);
               
        $clould_total= ClouldSubsidy::get_amount_total($start,$end,$sum);
        $base_clould=$base_clould_cost=(float)ClouldSubsidy::get_amount_base($start);
        
        $cache = Yii::$app->cache;
        $cost_total = $cache->get('allocate_cost');
        if ($cost_total === false) {
            $cost_total=[];
        }

        $data_allocate_amount = [];
        $data_allocate_num=[];
        $data_clould_amount = [];
        $data_clould_num = [];
            if($sum==1){                
                //天
                
                $amount_num_start=($allocate_total?strtotime(key($allocate_total)):$start)-86400;
                $clould_num_start=($clould_total?strtotime(key($clould_total)):($allocate_total?strtotime(key($allocate_total)):$start))-86400;
                if($amount_num_start<=$clould_num_start){
                    if($allocate_total){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                        $base_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']+$base_amount : $base_amount;
                        $data_allocate_amount[] = ['name' => $j, 'y' => $base_amount/10000]; 
                    }
                    $series['amount'][] = ['type' => 'area','zIndex'=>1, 'name' => '累计下拨额','color'=>'#7CB5EC', 'data' => $data_allocate_amount];
                
                    }
                    if($clould_total){
                    for ($i = $clould_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$clould_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);                  
                        $base_clould=isset($clould_total[$k]['amount']) ? (float) $clould_total[$k]['amount']+$base_clould : $base_clould;
                        $data_clould_amount[] = ['name' => $j, 'y' => $base_clould/10000];
                    }
                    $series['amount'][] = ['type' => 'area','zIndex'=>3, 'name' => '累计公有云补贴','color'=>'#F7A35C', 'data' => $data_clould_amount];          
                    }
                }else{
                    if($clould_total){
                    for ($i = $clould_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$clould_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);                  
                        $base_clould=isset($clould_total[$k]['amount']) ? (float) $clould_total[$k]['amount']+$base_clould : $base_clould;
                        $data_clould_amount[] = ['name' => $j, 'y' => $base_clould/10000];
                    }
                    $series['amount'][] = ['type' => 'area','zIndex'=>3, 'name' => '累计公有云补贴额','color'=>'#F7A35C', 'data' => $data_clould_amount];
                    }
                
                    if($allocate_total){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400) {
                        $k=date('Y-m-d', $i);
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                        $base_amount=isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']+$base_amount : $base_amount;
                        $data_allocate_amount[] = ['name' => $j, 'y' => $base_amount/10000]; 
                    }
                    $series['amount'][] = ['type' => 'area','zIndex'=>1, 'name' => '累计下拨额','color'=>'#7CB5EC', 'data' => $data_allocate_amount];
                    }
                }
                
                $old_cost_num= count($cost_total);
                for ($i = $amount_num_start<=$clould_num_start?$amount_num_start:$clould_num_start; $i <= $end; $i = $i + 86400){                  
                    $j = $end-($amount_num_start<=$clould_num_start?$amount_num_start:$clould_num_start)>=365*86400?date('Y.n.j', $i):date('n.j', $i);                    
                    if(isset($cost_total[$i])){
                        $cost=$cost_total[$i];
                    }else{
                        $k=date('Y-m-d', $i);
                        $base_clould_cost=isset($clould_total[$k]['amount']) ? (float) $clould_total[$k]['amount']+$base_clould_cost : $base_clould_cost;
                        $cost= sprintf("%.0f", (float) CorporationMeal::get_cost_total($i))+$base_clould_cost;
                        $cost_total[$i]=$cost;
                    }
                    
                    $data_amount_cost[] = ['name' => $j, 'y' => round($cost/10000,2)];
                }
                $series['amount'][] = ['type' => 'areaspline','zIndex'=>2, 'name' => '累计消耗额','color'=>'#90EE7E', 'data' => $data_amount_cost];
                if($old_cost_num!=count($cost_total)){
                    $query = CorporationMeal::find()->select(['SUM(amount)'])->createCommand()->getRawSql();
                    $dependency = new \yii\caching\DbDependency(['sql' => $query]);
                    $cache->set('allocate_cost', $cost_total, null, $dependency);
                }
                
            }elseif($sum==2){
                //周
                $amount_num_start=($allocate_total?strtotime(key($allocate_total)):strtotime(strftime("%Y-W%W",$start)));
                $clould_num_start=($clould_total?strtotime(key($clould_total)):($allocate_total?strtotime(key($allocate_total)):strtotime(strftime("%Y-W%W",$start))));
                if($amount_num_start<=$clould_num_start){
                    if($allocate_total){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_allocate_amount[] = ['name' => $j, 'y' => isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0];
                        $data_allocate_num[] = ['name' => $j, 'y' => isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0];
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                
                    }
                
                    if($clould_total){
                    for ($i = $clould_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$clould_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_clould_amount[] = ['name' => $j, 'y' => isset($clould_total[$k]['amount']) ? (float) $clould_total[$k]['amount']/10000 :0];
                        $data_clould_num[] = ['name' => $j, 'y' => isset($clould_total[$k]['num']) ? (float) $clould_total[$k]['num'] :0];
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期公有云补贴额', 'data' => $data_clould_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期公有云补贴数', 'data' => $data_clould_num,'yAxis'=>1,];
                    }
                }else{
                    if($clould_total){
                    for ($i = $clould_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$clould_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_clould_amount[] = ['name' => $j, 'y' => isset($clould_total[$k]['amount']) ? (float) $clould_total[$k]['amount']/10000 :0];
                        $data_clould_num[] = ['name' => $j, 'y' => isset($clould_total[$k]['num']) ? (float) $clould_total[$k]['num'] :0];
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期公有云补贴额', 'data' => $data_clould_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期公有云补贴数', 'data' => $data_clould_num,'yAxis'=>1,];
                    }
                    if($allocate_total){
                    for ($i = $amount_num_start; $i <= $end; $i = $i + 86400*7) {
                        $k=strftime("%Y-W%W",$i);
                        $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                        $j = $end-$amount_num_start>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                        //$base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_allocate_amount[] = ['name' => $j, 'y' => isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0];
                        $data_allocate_num[] = ['name' => $j, 'y' => isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0];
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                    }
                }
                
                $old_cost_num= count($cost_total);
                for ($i = $amount_num_start<=$clould_num_start?$amount_num_start:$clould_num_start; $i <= $end; $i = $i + 86400*7){
                    $l=($i + 86400*6)<$end?($i + 86400*6):$end;
                    $j = $end-($amount_num_start<=$clould_num_start?$amount_num_start:$clould_num_start)>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $l):date('n.j', $i).'-'.date('n.j', $l);
                                      
                    if(!isset($cost_total[$i])){                       
                        $cost_total[$i]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($i))+(float)ClouldSubsidy::get_amount_base($i);
                    }
                    if(!isset($cost_total[$l+86400])){
                       $cost_total[$l+86400]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($l+86400))+(float)ClouldSubsidy::get_amount_base($l+86400);
                    }
                       
                    
                    $cost=$cost_total[$l+86400]-$cost_total[$i];
                    $data_amount_cost[] = ['name' => $j, 'y' => round($cost/10000,2)];
                    
                   
                }
                $series['amount'][] = ['type' => 'spline','zIndex'=>5, 'name' => '当期消耗额', 'data' => $data_amount_cost];
                if($old_cost_num!=count($cost_total)){
                    $query = CorporationMeal::find()->select(['SUM(amount)'])->createCommand()->getRawSql();
                    $dependency = new \yii\caching\DbDependency(['sql' => $query]);
                    $cache->set('allocate_cost', $cost_total, null, $dependency);
                }
            }else{
                //月
                $amount_num_start=($allocate_total?strtotime(key($allocate_total)):strtotime(date("Y-m",$start)));
                $clould_num_start=($clould_total?strtotime(key($clould_total)):($allocate_total?strtotime(key($allocate_total)):strtotime(date("Y-m",$start))));

                if($amount_num_start<=$clould_num_start){
                    if($allocate_total){
                    for ($i = $amount_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
//                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_allocate_amount[] = ['name' => $j, 'y' => isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0];
                        $data_allocate_num[] = ['name' => $j, 'y' => isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0];
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                    }
                
                    if($clould_total){
                    for ($i = $clould_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
//                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_clould_amount[] = ['name' => $j, 'y' => isset($clould_total[$k]['amount']) ? (float) $clould_total[$k]['amount']/10000 :0];  
                        $data_clould_num[] = ['name' => $j, 'y' => isset($clould_total[$k]['num']) ? (float) $clould_total[$k]['num'] :0]; 
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期公有云补贴额', 'data' => $data_clould_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期公有云补贴数', 'data' => $data_clould_num,'yAxis'=>1,];
                    }
                }else{
                    if($clould_total){
                    for ($i = $clould_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
//                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_clould_amount[] = ['name' => $j, 'y' => isset($clould_total[$k]['amount']) ? (float) $clould_total[$k]['amount']/10000 :0];  
                        $data_clould_num[] = ['name' => $j, 'y' => isset($clould_total[$k]['num']) ? (float) $clould_total[$k]['num'] :0]; 
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>3, 'name' => '当期公有云补贴额', 'data' => $data_clould_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>1, 'name' => '当期公有云补贴数', 'data' => $data_clould_num,'yAxis'=>1,];
                    }
                    if($allocate_total){
                    for ($i = $amount_num_start; $i <= $end; $i= strtotime('+1 months',$i)) {
                        $k=date("Y-m",$i);
                        $j = date('Y.n', $i);
//                      $base_amount=isset($amount_num[$k]) ? (float) $amount_num[$k]['num']+$base_amount : $base_amount;
                        $data_allocate_amount[] = ['name' => $j, 'y' => isset($allocate_total[$k]['amount']) ? (float) $allocate_total[$k]['amount']/10000 :0];
                        $data_allocate_num[] = ['name' => $j, 'y' => isset($allocate_total[$k]['num']) ? (float) $allocate_total[$k]['num'] :0];
                    }
                    $series['amount'][] = ['type' => 'line','zIndex'=>4, 'name' => '当期下拨额', 'data' => $data_allocate_amount];
                    $series['amount'][] = ['type' => 'column','zIndex'=>2, 'name' => '当期下拨数', 'data' => $data_allocate_num,'yAxis'=>1,];
                    }
                }
                
                $old_cost_num= count($cost_total);
                for ($i = $amount_num_start<=$clould_num_start?$amount_num_start:$clould_num_start; $i <= $end; $i = strtotime('+1 months',$i)){
                    $l=strtotime('+1 months',$i)-86400<$end?strtotime('+1 months',$i)-86400:$end;
                    $j = date('Y.n', $i);                                      
                    if(!isset($cost_total[$i])){                       
                        $cost_total[$i]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($i))+(float)ClouldSubsidy::get_amount_base($i);
                    }
                    if(!isset($cost_total[$l+86400])){
                       $cost_total[$l+86400]= sprintf("%.0f", (float) CorporationMeal::get_cost_total($l+86400))+(float)ClouldSubsidy::get_amount_base($l+86400);
                    }
                  
                    $cost=$cost_total[$l+86400]-$cost_total[$i];
                    $data_amount_cost[] = ['name' => $j, 'y' => round($cost/10000,2)];
                  
                }
                $series['amount'][] = ['type' => 'spline','zIndex'=>5, 'name' => '当期消耗额', 'data' => $data_amount_cost];
                if($old_cost_num!=count($cost_total)){
                    $query = CorporationMeal::find()->select(['SUM(amount)'])->createCommand()->getRawSql();
                    $dependency = new \yii\caching\DbDependency(['sql' => $query]);
                    $cache->set('allocate_cost', $cost_total, null, $dependency);
                }
            }
            
        //下拨金额百分比
        $series['allocate_num']=[]; 
        $data_allocate=[];     
        $allocate_num= CorporationMeal::get_allocate_num($start,$end);
        foreach($allocate_num as $allocate){
            $data_allocate[] = ['name' =>floatval($allocate['amount']/10000).'万', 'y' => (int) $allocate['num']];
        }
        $series['allocate_num'][] = ['type' => 'pie','innerSize'=>'50%', 'name' => '数量', 'data' => $data_allocate];
        
        //BD下拨金额
        $series['allocate_bd']=[];       
        $data_allocate_bd=[]; 
        $changes=[];
        $bds=[];
        $allocate_bd= CorporationMeal::get_amount_total($start,$end,3,1);
        
        $groups = User::get_bd_color();
            
        foreach($allocate_bd as $allocate){
            $allocate['bd']=$allocate['bd']?$allocate['bd']:0;               
            $changes[$allocate['time']][$allocate['bd']]=(float) $allocate['amount']; 
            $bds[$allocate['bd']]=(isset($bds[$allocate['bd']])?$bds[$allocate['bd']]:0)+(float) $allocate['amount'];
        }
        ksort($changes);
        arsort($bds);
        
  
        foreach($changes as $key=>$change){
            foreach($bds as $b=>$bd){
                $data_allocate_bd[$key][] = ['name' =>$b?$groups[$b]['name']:'未分配', 'y' => isset($change[$b])?$change[$b]/10000:0];
            }
        
        }
        foreach($data_allocate_bd as $k=>$allocate){
            $series['allocate_bd'][] = ['type' => 'bar', 'name' => $k, 'data' => $allocate];
            
        }
        
     
        return $this->render('corporation', ['series' => $series,'drilldown'=>$drilldown, 'start' => $start, 'end' => $end,'sum'=>$sum]);
        
    }
    
    public function actionActivity() {

        $end = strtotime('today');
        $start = strtotime('-1 months +1 days',$end);
        $sum=Yii::$app->request->get('sum',1);
        $group=Yii::$app->request->get('group',1);

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]): $end;
        }
        $series['activity'] = [];
        
        //活跃数
        $activity_total = ActivityChange::get_activity_total($start-86400, $end,$sum,$group);
        $activity_change = ActivityChange::get_activity_total($start-86400, $end,$sum,$group,true);
        if($group==1){
           
            $data_total = [];
            $data_change = [];
            $data_per = [];
            foreach($activity_change as $change){
                $changes[date('Y.n.j',$change['start_time']+86400).'-'.date('Y.n.j',$change['end_time'])]=(int) $change['num'];                
            }
            foreach($activity_total as $total){
                $key=date('Y.n.j',$total['start_time']+86400).'-'.date('Y.n.j',$total['end_time']);
                $data_total[]=['name' =>$key , 'y' =>  (int) $total['num']];
                $data_change[]=['name' => $key, 'y' =>  isset($changes[$key])?$changes[$key]:0];
                $data_per[]=['name' => $key, 'y' => isset($changes[$key])?round($changes[$key]/(int)$total['num']*100,2):0];               
            }
            
            $series['activity'][] = ['type' => 'column', 'name' => '下拨企业数', 'data' => $data_total,'grouping'=>false,'borderWidth'=>0,'shadow'=>false];
            $series['activity'][] = ['type' => 'column', 'name' => '活跃企业数', 'data' => $data_change,'grouping'=>false,'borderWidth'=>0,'shadow'=>false,'dataLabels'=>['inside'=>true]];
            $series['activity'][] = ['type' => 'spline', 'name' => '活跃率', 'data' => $data_per,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1, 'dataLabels'=>['allowOverlap'=>true]];                           
        }else{
            
            $data_change=[];
            $data_per = [];
            $groups = User::get_bd_color();
            
            foreach($activity_change as $change){
                $change['bd_id']=$change['bd_id']?$change['bd_id']:0;
                $start_time=$change['start_time'];
                $end_time=$change['end_time'];
//                $start_time=date('n.j',$change['start_time']+86400);
//                $end_time=date('n.j',$change['end_time']);
                if($sum){
                    $et=date('Y.n.j',$change['end_time']);//周
                }else{
                    $et=date('Y.n',$change['end_time']);//月
                }
                $changes[$et][$change['bd_id']]=(int) $change['num'];
                $changes[$et]['start_time']=!isset($changes[$et]['start_time'])||$start_time<$changes[$et]['start_time']?$start_time:$changes[$et]['start_time'];
                $changes[$et]['end_time']=!isset($changes[$et]['end_time'])||$end_time>$changes[$et]['end_time']?$end_time:$changes[$et]['end_time'];
            }
            

            foreach($activity_total as $total){
                if($sum){
                    $et2=date('Y.n.j',$total['end_time']);
                }else{
                    $et2=date('Y.n',$total['end_time']);
                }
               
                $key=$end-$start>=365*86400?date('Y.n.j',$changes[$et2]['start_time']+86400).'-'.date('Y.n.j',$changes[$et2]['end_time']):date('n.j',$changes[$et2]['start_time']+86400).'-'.date('n.j',$changes[$et2]['end_time']);
//              $key=$changes[$et2]['start_time'].'-'.$changes[$et2]['end_time'];                
                $total['bd_id']=$total['bd_id']?$total['bd_id']:0;
                $data_change[$total['bd_id']][]=['name' => $key, 'y' =>  isset($changes[$et2][$total['bd_id']])?$changes[$et2][$total['bd_id']]:0];
                $data_per[$total['bd_id']][]=['name' => $key, 'y' => isset($changes[$et2][$total['bd_id']])?round($changes[$et2][$total['bd_id']]/(int)$total['num']*100,2):0];               
            }
            
            foreach ($data_change as $gid=>$data){
                $series['activity'][] = ['type' => 'column', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000'];
               
            }
            foreach ($data_per as $gid=>$per){
                 $series['activity'][] = ['type' => 'spline', 'name' => $gid&&isset($groups[$gid]['name'])?$groups[$gid]['name']:'未分配', 'data' => $per,'tooltip'=>['valueSuffix'=>'%'],'yAxis'=>1,'color'=>$gid&&isset($groups[$gid]['color'])?'#'.$groups[$gid]['color']:'#FF0000'];
            }
                               
        }
        
        //活跃项目
        $series['item']=[];       
        $data_item=[]; 
        
        $items=[];
        $items['沉默企业']=(int) ActivityChange::get_activity_item($start-86400, $end,null,false);
        $items['项目管理']=(int) ActivityChange::get_activity_item($start-86400, $end,['projectman_usercount','projectman_issuecount']);
        $items['代码托管']=(int) ActivityChange::get_activity_item($start-86400, $end,'codehub_commitcount');
        $items['代码检查']=(int) ActivityChange::get_activity_item($start-86400, $end,'codecheck_execount');
        $items['测试']=(int) ActivityChange::get_activity_item($start-86400, $end,'testman_totalexecasecount');
        $items['部署']=(int) ActivityChange::get_activity_item($start-86400, $end,'deploy_execount');
        $items['编译构建']=(int) ActivityChange::get_activity_item($start-86400, $end,['codeci_allbuildcount','codeci_buildtotaltime']);
        
        arsort($items);
//        $fv= reset($items);
//        $fk= key($items);
//        unset($items[$fk]);
//        $items[$fk]=$fv;
        
        foreach ($items as $key=>$item){
            $data_item[] = ['name' =>  $key, 'y' =>$item];
        }
        $series['item'][] = ['type' => 'pie','name' => '数量', 'data' => $data_item];
        
        return $this->render('activity', ['series' => $series, 'start' => $start, 'end' => $end,'sum'=>$sum,'group'=>$group]);
    }
    
    public function actionTrain() {
        
        $end = strtotime('today')+ 86399;
        $start = strtotime('-30 days',$end);
        $sum=Yii::$app->request->get('sum',1);//1-天；2-周；3-月
        $group=Yii::$app->request->get('group',1);//1-总；0-个人

        if (Yii::$app->request->get('range')) {
            $range = explode('~', Yii::$app->request->get('range'));
            $start = isset($range[0]) ? strtotime($range[0]) : $start;
            $end = isset($range[1]) && (strtotime($range[1]) < $end) ? strtotime($range[1]) + 86399 : $end;
        }
        
        $series['num']=[];
        $series['type']=[];

        //趋势
        $train_num = Train::get_train_num($start, $end, Train::STAT_END,$sum,$group);
        $train_type = Train::get_train_type($start, $end, Train::STAT_END,$group);
               
        if($group==1){
            $data_train_num = [];            
            if($sum==1){
                //天
                for ($i = ($train_num===true?strtotime(key($train_num)):$start); $i < $end; $i = $i + 86400) {
                    $k=date('Y-m-d', $i);
                    $j = $end-($train_num===true?strtotime(key($train_num)):$start)>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                    $data_train_num[] = ['name' => $j, 'y' => isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0];          
                }
            }elseif($sum==2){
                //周
                for ($i = ($train_num?strtotime(key($train_num)):strtotime(strftime("%Y-W%W",$start))); $i < $end; $i = $i + 86400*7) {
                    $k=strftime("%Y-W%W",$i);
                    $j = $end-($train_num?strtotime(key($train_num)):strtotime(strftime("%Y-W%W",$start)))>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $i + 86400*7-1):date('n.j', $i).'-'.date('n.j', $i + 86400*7-1);
                    $data_train_num[] = ['name' => $j, 'y' => isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0];          
                }
            }else{
                //月
                for ($i = ($train_num?strtotime(key($train_num)):strtotime(date("Y-m",$start))); $i < $end; $i= strtotime('+1 months',$i)) {
                    $k=date("Y-m",$i);
                    $j = date('Y.n', $i);
                    $data_train_num[] = ['name' => $j, 'y' => isset($train_num[$k]) ? (int) $train_num[$k]['num'] : 0];         
                }
            }
            $series['num'][] = ['type' => 'line', 'name' => '次数', 'data' => $data_train_num,'showInLegend'=>false];
            
            //类型
            $data_train_type = [];
            foreach($train_type as $type){
                 $data_train_type[] = ['name' => Train::$List['train_type'][$type['train_type']], 'y' => (int) $type['num']];
                 //$series['type'][] = ['type' => 'column', 'name' => Train::$List['train_type'][$type['train_type']], 'data'=>[(int) $type['num']]];
            }            
            $series['type'][] = ['type' => 'column', 'name' => '次数', 'data' => $data_train_type,'showInLegend'=>false,'colorByPoint'=>false];            
        }else{
                     
            $groups = User::get_user_color();
            
            //次数
            $users_num=[];
            $data_num_total=[];
            $data_train_num = [];
            
            
            foreach ($train_num as $total){
                if(!in_array($total['user_id'], $users_num)){
                    $users_num[]=$total['user_id'];
                }              
                $data_num_total[$total['time']][$total['user_id']]=$total['num'];
            }
            if($sum==1){
                for ($i = ($data_num_total===true?strtotime(key($data_num_total)):$start); $i < $end; $i = $i + 86400) {
                    $k=date('Y-m-d', $i);
                    $j = $end-($data_num_total===true?strtotime(key($data_num_total)):$start)>=365*86400?date('Y.n.j', $i):date('n.j', $i);
                    foreach($users_num as $user){
                        $data_train_num[$user][] = ['name' => $j, 'y' => isset($data_num_total[$k][$user]) ? (int) $data_num_total[$k][$user] : 0];
                    }
                }
            }elseif($sum==2){
                for ($i = ($data_num_total===true?strtotime(key($data_num_total)):strtotime(strftime("%Y-W%W",$start))); $i < $end; $i = $i + 86400*7) {
                    $k=strftime("%Y-W%W",$i);
                    $j = $end-($data_num_total===true?strtotime(key($data_num_total)):strtotime(strftime("%Y-W%W",$start)))>=365*86400?date('Y.n.j', $i).'-'.date('Y.n.j', $i + 86400*7-1):date('n.j', $i).'-'.date('n.j', $i + 86400*7-1);
                    foreach($users_num as $user){
                        $data_train_num[$user][] = ['name' => $j, 'y' => isset($data_num_total[$k][$user]) ? (int) $data_num_total[$k][$user] : 0];                    
                    }      
                }
            }else{
                for ($i = ($data_num_total===true?strtotime(key($data_num_total)):strtotime(date("Y-m",$start))); $i < $end; $i= strtotime('+1 months',$i)) {
                    $k=date("Y-m",$i);
                    $j = date('Y.n', $i);
                    foreach($users_num as $user){
                        $data_train_num[$user][] = ['name' => $j, 'y' => isset($data_num_total[$k][$user]) ? (int) $data_num_total[$k][$user] : 0];                   
                    }         
                }
            }
            
            foreach ($data_train_num as $gid=>$data){
                $series['num'][] = ['type' => 'line', 'name' => $gid?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&$groups[$gid]['color']?'#'.$groups[$gid]['color']:''];              
            }
            
            //类型
            $users_type=[];
            $data_type_total=[];
            $data_train_type = [];
           
            foreach ($train_type as $t){
                if(!in_array($t['user_id'], $users_type)){
                    $users_type[]=$t['user_id'];
                }              
                $data_type_total[$t['train_type']][$t['user_id']]=$t['num'];
            }
            
            foreach($data_type_total as $k=>$type){
                foreach($users_type as $user){
                    $data_train_type[$user][] = ['name' => Train::$List['train_type'][$k], 'y' => isset($data_type_total[$k][$user]) ? (int) $data_type_total[$k][$user] : 0];                   
                }      
            }
            
            foreach ($data_train_type as $gid=>$data){
                $series['type'][] = ['type' => 'column', 'name' => $gid?$groups[$gid]['name']:'未分配', 'data' => $data,'color'=>$gid&&$groups[$gid]['color']?'#'.$groups[$gid]['color']:''];              
            }
                
            
            
            
        }
        return $this->render('train', ['series' => $series, 'start' => $start, 'end' => $end,'sum'=>$sum,'group'=>$group]);
    }
    
   

}
