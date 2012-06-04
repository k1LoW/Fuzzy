<?php

App::uses('Model', 'Model');
App::uses('AppModel', 'Model');

class FuzzyPost extends CakeTestModel{

    public $name = 'FuzzyPost';

    public $actsAs = array('Fuzzy.Adjustable');

    public $validate = array('title' => '/^[0-9a-zA-Z]*$/',
                             'title_mb' => '/^[^0-9a-zA-Z]*$/');
}

class AdjustableTestCase extends CakeTestCase{

    public $fixtures = array('plugin.fuzzy.fuzzy_post');

    function setUp() {
        $this->FuzzyPost = new FuzzyPost(); // jpn: 初期化するため
        $this->FuzzyPostFixture = ClassRegistry::init('FuzzyPostFixture');
    }

    function tearDown() {
        unset($this->FuzzyPost);
        unset($this->FuzzyPostFixture);
    }

    /**
     * testSaveAlphaNumericValidationError
     *
     * en:
     * jpn: Adjustableの設定をせずにtitleフィールドにマルチバイトの文字列、title_mbに半角英数を入れたらバリデーションエラー
     */
    function testSaveAlphaNumericValidationError(){
        $data = array('FuzzyPost' => array('title' => 'Ｔｉｔｌｅ３',
                                           'title_mb' => 'title3',
                                           'body' => 'Validation Error'));
        $result = $this->FuzzyPost->save($data);
        $this->assertFalse($result);

        $expected = array('title', 'title_mb');
        $this->assertIdentical($expected, array_keys($this->FuzzyPost->validationErrors));
    }

    /**
     * testSaveMbConvertKana
     *
     * en:
     * jpn: Adjustableのmb_convert_kana設定をすることでbeforeValidateの段階でフィールドにmb_convert_kana()を適用できる
     */
    public function testSaveMbConvertKana(){
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'title',
                                                      'mb_convert_kana' => 'a',
                                                      'encoding' => 'UTF-8'),
                                                array('field' => 'title_mb',
                                                      'mb_convert_kana' => 'A',
                                                      'encoding' => 'UTF-8'),
                                                );
        $data = array('FuzzyPost' => array('title' => 'Ｔｉｔｌｅ３',
                                           'title_mb' => 'Title3',
                                           'body' => 'Save OK'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['title'], 'Title3');
        $this->assertIdentical($result['FuzzyPost']['title_mb'], 'Ｔｉｔｌｅ３');
    }

    /**
     * testSaveReplace
     *
     * en:
     * jpn: Adjustableのreplase設定をすることでbeforeValidateの段階でフィールドにmb_str_replace()を適用できる
     */
    public function testSaveReplace(){
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'title',
                                                      'replace' => array('t', 'a'),
                                                      'encoding' => 'UTF-8'),
                                                );
        $data = array('FuzzyPost' => array('title' => 'Title3',
                                           'title_mb' => 'タイトル３',
                                           'body' => 'Save OK'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['title'], 'Tiale3');

        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'title',
                                                      'replace' => array(array('t', 'i', ' '), array('a', 'b', '')),
                                                      'encoding' => 'UTF-8'),
                                                );
        $data = array('FuzzyPost' => array('title' => 'Tit l e3',
                                           'title_mb' => 'タイトル３',
                                           'body' => 'Save OK'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['title'], 'Tbale3');
    }

    /**
     * testSaveTelNoSplit
     *
     * en:
     * jpn: Adjustableのphone_split設定をすることでbeforeValidateの段階で電話番号を分割して保存できる
     */
    public function testSaveTelNoSplit(){
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'tel_no',
                                                      'phone_split' => array('tel_no1', 'tel_no2', 'tel_no3'),
                                                      ),
                                                );
        $data = array('FuzzyPost' => array('title' => 'title4',
                                           'title_mb' => 'タイトル４',
                                           'body' => 'Tel No Split',
                                           'tel_no' => '09212345678'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['tel_no1'], '092');
        $this->assertIdentical($result['FuzzyPost']['tel_no2'], '1234');
        $this->assertIdentical($result['FuzzyPost']['tel_no3'], '5678');


        $data = array('FuzzyPost' => array('title' => 'title4',
                                           'title_mb' => 'タイトル４',
                                           'body' => 'Tel No Split',
                                           'tel_no' => '092-12345678'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['tel_no1'], '092');
        $this->assertIdentical($result['FuzzyPost']['tel_no2'], '1234');
        $this->assertIdentical($result['FuzzyPost']['tel_no3'], '5678');
    }

    /**
     * testSaveZipSplit
     *
     * en:
     * jpn: Adjustableのpostal_split設定をすることでbeforeValidateの段階で郵便番号を分割して保存できる
     */
    public function testSaveZipSplit(){
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'zip',
                                                      'postal_split' => array('zip1', 'zip2'),
                                                      ),
                                                );
        $data = array('FuzzyPost' => array('title' => 'title4',
                                           'title_mb' => 'タイトル４',
                                           'body' => 'Zip Split',
                                           'zip' => '8100042'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['zip1'], '810');
        $this->assertIdentical($result['FuzzyPost']['zip2'], '0042');

        // en:
        // jpn: mb_convert_kanaを組み合わせて全角数字でも適切に郵便番号を分割して保存できる
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'zip',
                                                      'mb_convert_kana' => 'n',
                                                      'postal_split' => array('zip1', 'zip2'),
                                                      ),
                                                );

        $data = array('FuzzyPost' => array('title' => 'title4',
                                           'title_mb' => 'タイトル４',
                                           'body' => 'Zip Split',
                                           'zip' => ' ８10ー0０42 '));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['zip1'], '810');
        $this->assertIdentical($result['FuzzyPost']['zip2'], '0042');
    }

    /**
     * testSaveAddressSplit
     *
     * en:
     * jpn: Adjustableのaddress_split設定をすることでbeforeValidateの段階で住所情報を分割して保存できる
     */
    public function testSaveAddressSplit(){
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'address',
                                                      'address_split' => array('prefecture', 'city', 'town'),
                                                      ),
                                                );
        $data = array('FuzzyPost' => array('title' => 'title4',
                                           'title_mb' => 'タイトル４',
                                           'body' => 'Address Split',
                                           'address' => '福岡県福岡市中央区大名2-4-22'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['prefecture'], '福岡県');
        $this->assertIdentical($result['FuzzyPost']['city'], '福岡市中央区');
        $this->assertIdentical($result['FuzzyPost']['town'], '大名2-4-22');

        // en:
        // jpn: 住所を3分割ではなく2分割する
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'address',
                                                      'address_split' => array('prefecture',
                                                                               'city',
                                                                               'city' // 住所を2つにわける
                                                                               ),
                                                      ),
                                                );
        $data = array('FuzzyPost' => array('title' => 'title4',
                                           'title_mb' => 'タイトル４',
                                           'body' => 'Address Split',
                                           'address' => '福岡県福岡市中央区大名2-4-22'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['prefecture'], '福岡県');
        $this->assertIdentical($result['FuzzyPost']['city'], '福岡市中央区大名2-4-22');

        // en:
        // jpn: 空白などが入っていてもある程度考慮して分割する(各項目の前後空白を削除する)
        //      Tips: trimやreplaceパラメータを利用してあらかじめ空白を排除することも可能
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'address',
                                                      'address_split' => array('prefecture', 'city', 'town'),
                                                      ),
                                                );
        $data = array('FuzzyPost' => array('title' => 'title4',
                                           'title_mb' => 'タイトル４',
                                           'body' => 'Address Split',
                                           'address' => '福岡県　福岡市　中央区　大名　2-4-22'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['prefecture'], '福岡県');
        $this->assertIdentical($result['FuzzyPost']['city'], '福岡市　中央区');
        $this->assertIdentical($result['FuzzyPost']['town'], '大名　2-4-22');
    }
}
