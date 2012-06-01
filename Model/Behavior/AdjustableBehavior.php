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
                          'adjuster' => array('AdjusterJa', 'Fuzzy.Lib'),
                          );
        // Default settings
        $this->settings[$model->alias] = Set::merge($defaults, $settings);

        $adjuster = $this->settings[$model->alias]['adjuster'];
        App::uses($adjuster[0], $adjuster[1]);
        $this->adjuster = new $adjuster[0];
    }

    /**
     * beforeValidate
     *
     * @param $model
     * @return
     */
    public function beforeValidate(Model $model, $options = array()){
        $modelName = $model->alias;
        $convertFields = Set::combine($model->convertFields, '/field' , '/');

        foreach ($model->data[$modelName] as $fieldName => $value) {
            if (empty($convertFields[$fieldName])) {
                continue;
            }
            if (empty($value)) {
                continue;
            }

            // trim
            if (!empty($convertFields[$fieldName]['trim'])) {
                if ($convertFields[$fieldName]['trim'] === true) {
                    $value = trim($value);
                } else {
                    $value = trim($value, $convertFields[$fieldName]['trim']);
                }
                $model->data[$modelName][$fieldName] = $value;
            }

            // mb_convert_kana
            if (!empty($convertFields[$fieldName]['mb_convert_kana'])) {
                $encoding = empty($convertFields[$fieldName]['encoding']) ? Configure::read('App.encoding') : $convertFields[$fieldName]['encoding'];
                $value = mb_convert_kana($value, $convertFields[$fieldName]['mb_convert_kana'], $encoding);
                $model->data[$modelName][$fieldName] = $value;
            }

            // phone_split
            if (!empty($convertFields[$fieldName]['phone_split'])) {
                $phoneNos = $this->adjuster->splitPhoneNo($value);
                $model->data[$modelName][$convertFields[$fieldName]['phone_split'][0]] = $phoneNos[0];
                $model->data[$modelName][$convertFields[$fieldName]['phone_split'][1]] = $phoneNos[1];
                $model->data[$modelName][$convertFields[$fieldName]['phone_split'][2]] = $phoneNos[2];
            }

            // postal_split
            if (!empty($convertFields[$fieldName]['postal_split'])) {
                $zips = $this->adjuster->splitZipCode($value);
                $model->data[$modelName][$convertFields[$fieldName]['postal_split'][0]] = $zips[0];
                $model->data[$modelName][$convertFields[$fieldName]['postal_split'][1]] = $zips[1];
            }
        }
        return true;
    }
}
