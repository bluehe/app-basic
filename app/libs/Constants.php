<?php
/**
 * Author: lf
 * Blog: https://blog.feehi.com
 * Email: job@feehi.com
 * Created at: 2016-10-16 17:15
 */

namespace app\libs;

use yii;
use yii\base\InvalidParamException;

class Constants
{

    const YesNo_Yes = 1;
    const YesNo_No = 0;

    public static function getYesNoItems($key = null)
    {
        $items = [
            self::YesNo_Yes => yii::t('app', 'Yes'),
            self::YesNo_No => yii::t('app', 'No'),
        ];
        return self::getItems($items, $key);
    }

    public static function getWebsiteStatusItems($key = null)
    {
        $items = [
            self::YesNo_Yes => yii::t('app', 'Opened'),
            self::YesNo_No => yii::t('app', 'Closed'),
        ];
        return self::getItems($items, $key);
    }

    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public static function getUserStatusItems($key = null)
    {
        $items = [
            self::STATUS_DELETED => '删除',
            self::STATUS_ACTIVE => '正常',
        ];
        return self::getItems($items, $key);
    }

    const Status_Enable = 1;
    const Status_Desable = 0;

    public static function getStatusItems($key = null)
    {
        $items = [
            self::Status_Enable => yii::t('app', 'Enable'),
            self::Status_Desable => yii::t('app', 'Disable'),
        ];
        return self::getItems($items, $key);
    }

    private static function getItems($items, $key = null)
    {
        if ($key !== null) {
            if (key_exists($key, $items)) {
                return $items[$key];
            }
            throw new InvalidParamException( 'Unknown key:' . $key );
        }
        return $items;
    }

}
