<?php
class AdjustableBehavior extends ModelBehavior {

    public $settings = array();
    private $adjuster;

    /**
     * setUp
     *
     * @param $model
     * @param $settings
     */
    public function setUp(Model $model, $settings = array()){
        $defaults = array(
                          'adjuster' => array('Lib', 'Fuzzy.AdjusterJa'),
                          'autoAdjust' => true,
                          );
        // Default settings
        $this->settings[$model->alias] = Set::merge($defaults, $settings);

        $adjuster = $this->settings[$model->alias]['adjuster'];
        App::import($adjuster[0], $adjuster[1]);
        $className = preg_replace('/^.*\./','', $adjuster[1]);
        $this->adjuster = new $className;
    }

    /**
     * beforeValidate
     *
     * @param $model
     * @return
     */
    public function beforeValidate(Model $model, $options = array()){
        if ($this->settings[$model->alias]['autoAdjust']) {
            $model->data = $this->adjust($model, $model->data);
        }
        return true;
    }

    /**
     * adjust
     *
     * @param Model $model
     */
    public function adjust(Model $model, $data){
        $modelName = $model->alias;
        if (empty($model->convertFields)) {
            return $data;
        }
        $convertFields = Set::combine($model->convertFields, '/field' , '/');

        foreach ($data[$modelName] as $fieldName => $value) {
            if (empty($convertFields[$fieldName])) {
                continue;
            }
            if (empty($value)) {
                continue;
            }

            // mb_convert_kana
            if (!empty($convertFields[$fieldName]['mb_convert_kana'])) {
                $encoding = empty($convertFields[$fieldName]['encoding']) ? Configure::read('App.encoding') : $convertFields[$fieldName]['encoding'];
                $value = mb_convert_kana($value, $convertFields[$fieldName]['mb_convert_kana'], $encoding);
                $data[$modelName][$fieldName] = $value;
            }

            // trim
            if (!empty($convertFields[$fieldName]['trim'])) {
                if ($convertFields[$fieldName]['trim'] === true) {
                    $value = trim($value);
                } else {
                    $value = trim($value, $convertFields[$fieldName]['trim']);
                }
                $data[$modelName][$fieldName] = $value;
            }

            // replace
            if (!empty($convertFields[$fieldName]['replace'])) {
                $encoding = empty($convertFields[$fieldName]['encoding']) ? Configure::read('App.encoding') : $convertFields[$fieldName]['encoding'];
                if (function_exists('mb_strlen')) {
                    $value = $this->mb_str_replace($convertFields[$fieldName]['replace'][0], $convertFields[$fieldName]['replace'][1], $value, $encoding);
                } else {
                    $value = $this->str_replace($convertFields[$fieldName]['replace'][0], $convertFields[$fieldName]['replace'][1], $value);
                }
                $data[$modelName][$fieldName] = $value;
            }

            // phone_split
            if (!empty($convertFields[$fieldName]['phone_split'])) {
                $phoneNos = $this->adjuster->splitPhoneNo($value);
                if (is_string($convertFields[$fieldName]['phone_split'])) {
                    $data[$modelName][$fieldName] = implode($convertFields[$fieldName]['phone_split'], $phoneNos);
                } else {
                    $data[$modelName][$convertFields[$fieldName]['phone_split'][0]] = $phoneNos[0];
                    $data[$modelName][$convertFields[$fieldName]['phone_split'][1]] = $phoneNos[1];
                    $data[$modelName][$convertFields[$fieldName]['phone_split'][2]] = $phoneNos[2];
                }
            }

            // postal_split
            if (!empty($convertFields[$fieldName]['postal_split'])) {
                $zips = $this->adjuster->splitZipCode($value);
                if (is_string($convertFields[$fieldName]['postal_split'])) {
                    $data[$modelName][$fieldName] = implode($convertFields[$fieldName]['postal_split'], $zips);
                } else {
                    $data[$modelName][$convertFields[$fieldName]['postal_split'][0]] = $zips[0];
                    $data[$modelName][$convertFields[$fieldName]['postal_split'][1]] = $zips[1];
                }
            }

            // address_split
            if (!empty($convertFields[$fieldName]['address_split'])) {
                $addresses = $this->adjuster->splitAddress($value);
                $data[$modelName][$convertFields[$fieldName]['address_split'][0]] = '';
                $data[$modelName][$convertFields[$fieldName]['address_split'][1]] = '';
                $data[$modelName][$convertFields[$fieldName]['address_split'][2]] = '';
                $data[$modelName][$convertFields[$fieldName]['address_split'][0]] = $addresses[0];
                $data[$modelName][$convertFields[$fieldName]['address_split'][1]] .= $addresses[1];
                $data[$modelName][$convertFields[$fieldName]['address_split'][2]] .= $addresses[2];
            }
        }
        return $data;
    }

    /**
     * マルチバイト対応 str_replace()
     *
     * @package     mb_str_replace
     * @version     Release 3
     * @author      HiNa <hina@bouhime.com>
     * @copyright   Copyright (C) 2006-2007,2011 by HiNa <hina@bouhime.com>. All rights reserved.
     * @license     https://github.com/fetus-hina/mb_str_replace/blob/master/LICENSE 2-cause BSD License(Simplified BSD License)
     * @link        http://fetus.k-hsu.net/document/programming/php/mb_str_replace.html
     * @link        https://github.com/fetus-hina/mb_str_replace
     */
    /**
     * マルチバイト対応 str_replace()
     *
     * @param   mixed   $search     検索文字列（またはその配列）
     * @param   mixed   $replace    置換文字列（またはその配列）
     * @param   mixed   $subject    対象文字列（またはその配列）
     * @param   string  $encoding   文字列のエンコーディング(省略: 内部エンコーディング)
     *
     * @return  mixed   subject 内の search を replace で置き換えた文字列
     *
     * この関数の $search, $replace, $subject は配列に対応していますが、
     * $search, $replace が配列の場合の挙動が PHP 標準の str_replace() と異なります。
     */
    public function mb_str_replace($search, $replace, $subject, $encoding = 'auto') {
        if(!is_array($search)) {
            $search = array($search);
        }
        if(!is_array($replace)) {
            $replace = array($replace);
        }
        if(strtolower($encoding) === 'auto') {
            $encoding = mb_internal_encoding();
        }

        // $subject が複数ならば各要素に繰り返し適用する
        if(is_array($subject) || $subject instanceof Traversable) {
            $result = array();
            foreach($subject as $key => $val) {
                $result[$key] = mb_str_replace($search, $replace, $val, $encoding);
            }
            return $result;
        }

        $currentpos = 0;    // 現在の検索開始位置
        while(true) {
            // $currentpos 以降で $search のいずれかが現れる位置を検索する
            $index = -1;    // 見つけた文字列（最も前にあるもの）の $search の index
            $minpos = -1;   // 見つけた文字列（最も前にあるもの）の位置
            foreach($search as $key => $find) {
                if($find == '') {
                    continue;
                }
                $findpos = mb_strpos($subject, $find, $currentpos, $encoding);
                if($findpos !== false) {
                    if($minpos < 0 || $findpos < $minpos) {
                        $minpos = $findpos;
                        $index = $key;
                    }
                }
            }

            // $search のいずれも見つからなければ終了
            if($minpos < 0) {
                break;
            }

            // 置換実行
            $r = array_key_exists($index, $replace) ? $replace[$index] : '';
            $subject =
                mb_substr($subject, 0, $minpos, $encoding) .    // 置換開始位置より前
                $r .                                            // 置換後文字列
                mb_substr(                                      // 置換終了位置より後ろ
                          $subject,
                          $minpos + mb_strlen($search[$index], $encoding),
                          mb_strlen($subject, $encoding),
                          $encoding);

            // 「現在位置」を $r の直後に設定
            $currentpos = $minpos + mb_strlen($r, $encoding);
        }
        return $subject;
    }
}
