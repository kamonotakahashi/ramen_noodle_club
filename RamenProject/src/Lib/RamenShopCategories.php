<?php

namespace App\Lib;

use App\Enum\BusinessHourStatus;

use App\Exceptions\BusinessHourStatusChangeFailureException;

class RamenShopCategories {

    public function __construct(){}

    /**
     * WordPressの営業中かどうかを判別しているカテゴリーを変更する
     * @param  Int $id        店舗ID
     * @param  Int $status    営業中かどうかのステータス
     * @return Bool           ステータス変更ができた場合、True。そうでない場合、False
     */
    public function changeCategoriesStatus($id, $status) :Bool
    {
        try {
            $result = null;
            $currentCategories = wp_get_post_categories($id);
            if($status == BusinessHourStatus::LAST_ORDER_BUSINESS_HOURS || $status == BusinessHourStatus::OPEN_BUSINESS_HOURS) {
                $result = wp_set_post_categories($id, $this->addCategoriesId($currentCategories, 1), true);
            } else {
                $result = wp_set_post_categories($id, $this->removeCategoriesId($currentCategories, 1), false);
            }

            if( is_wp_error($result) || $result == false || $result == '' ) {
                throw new BusinessHourStatusChangeFailureException([
                    "ramenShopId"           => $id,
                    "business_hours_status" => $status
                ]);
            }
            return true;
        } catch (BusinessHourStatusChangeFailureException $e) {
            return false;
        }
    }

    private function removeCategoriesId($array, $ignoreValue)
    {
        $pos = array_search($ignoreValue, $array);
        $results = [];
        if($pos !== false) {
            $array[$pos] = null;
        }
        foreach($array as $value) {
            $results[] = $value;
        }
        return $array;
    }

    private function addCategoriesId($array, $addValue)
    {
        $pos = array_search($addValue, $array);
        if($pos === false) {
            $array[] = $addValue;
        }
        return $array;
    }
}
