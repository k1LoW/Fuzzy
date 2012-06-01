<?php
App::uses('FormHelper', 'View/Helper');

class FuzzyFormHelper extends FormHelper {
    // http://tanaka.sakura.ad.jp/archives/001071.html
    public function end($options = null) {
        $out = $this->hidden('_Fuzzy.detectstr', array(
                                                      'value' => '文字',
                                                      'id' => 'Fuzzy' . mt_rand()
                                                    ));
        return $out . parent::end($options);
    }
}