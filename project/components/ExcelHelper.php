<?php

namespace project\components;

use Yii;
use yii\helpers\ArrayHelper;
use project\models\User;
use project\models\Corporation;
use project\models\Parameter;
use project\models\Industry;
use project\models\Meal;


class ExcelHelper {

    public static function set_corporation_excel($objSheet,$line_num=700) {
        $line_bd= implode(',', User::get_bd());
        for($i=2;$i<=$line_num;$i++){
            //BD选择
            $objSheet->getCell('D'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(false)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('客户经理')  
                -> setFormula1('"'.$line_bd.'"'); 
        }
        
        $line_industry= implode(',', Industry::getIndustriesName());
        for($i=2;$i<=$line_num;$i++){
            $objSheet->getCell('F'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(false)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('行业')  
                -> setFormula1('"'.$line_industry.'"'); 
        }
        
        $line_stat= implode(',', Corporation::$List['stat']);
        for($i=2;$i<=$line_num;$i++){
            //状态选择
            $objSheet->getCell('C'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(false)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('状态')  
                -> setFormula1('"'.$line_stat.'"'); 
        }
        
        $line_set = implode(',', Meal::get_meal());
        for($i=2;$i<=$line_num;$i++){
            //意向套餐选择
            $objSheet->getCell('I'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(true)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('意向套餐')  
                -> setFormula1('"'.$line_set.'"'); 
        }
        
        $line_park = implode(',', Parameter::get_type('contact_park'));
        for($i=2;$i<=$line_num;$i++){
            $objSheet->getCell('G'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(false)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('所属园区')  
                -> setFormula1('"'.$line_park.'"'); 
        }
        
        $line_develop_pattern = implode(',', Parameter::get_type('develop_pattern'));
        for($i=2;$i<=$line_num;$i++){
            $objSheet->getCell('X'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(true)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('开发模式')  
                -> setFormula1('"'.$line_develop_pattern.'"'); 
        }
        
        $line_develop_scenario = implode(',', Parameter::get_type('develop_scenario'));
        for($i=2;$i<=$line_num;$i++){
            $objSheet->getCell('Y'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(true)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('开发场景')  
                -> setFormula1('"'.$line_develop_scenario.'"'); 
        }
        
        $line_develop_science = implode(',', Parameter::get_type('develop_science'));
        for($i=2;$i<=$line_num;$i++){
            $objSheet->getCell('Z'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(true)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('开发环境')  
                -> setFormula1('"'.$line_develop_science.'"'); 
        }
        
        $line_develop_language = implode(',', Parameter::get_type('develop_language'));
        for($i=2;$i<=$line_num;$i++){
            $objSheet->getCell('AA'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(true)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('开发语言')  
                -> setFormula1('"'.$line_develop_language.'"'); 
        }
        
        $line_develop_IDE = implode(',', Parameter::get_type('develop_IDE'));
        for($i=2;$i<=$line_num;$i++){
            $objSheet->getCell('AB'.$i)->getDataValidation() -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)  
                -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
                -> setAllowBlank(true)  
                -> setShowInputMessage(true)  
                -> setShowErrorMessage(true)  
                -> setShowDropDown(true)  
                -> setErrorTitle('输入的值有误')  
                -> setError('您输入的值不在下拉框列表内.')  
                -> setPromptTitle('开发IDE')  
                -> setFormula1('"'.$line_develop_IDE.'"'); 
        }
               
        return true;
    }
    
    public static function excel_set_headers($format,$file_name) {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(preg_match('/msie/', $ua) || preg_match('/edge/', $ua)) { //判断是否为IE或Edge浏览器
            $file_name = str_replace('+', '%20', urlencode($file_name)); //使用urlencode对文件名进行重新编码
        }
        if ($format == 'Excel5') {
            header('Content-Type: application/vnd.ms-excel');
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }
        header('Content-Disposition: attachment;filename="' . $file_name .($format == 'Excel5'?'.xls':'.xlsx').'"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
    }
    
    public static function execute_array_label($sheetData) {
        $keys = ArrayHelper::remove($sheetData, '1');

        $new_data = [];

        foreach ($sheetData as $values) {
           
            if(array_filter($values)){
                $new_data[] = array_combine($keys, $values);
            }
        }

        return $new_data;
    }

}
