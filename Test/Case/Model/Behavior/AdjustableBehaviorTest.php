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
     * testSaveTrim
     *
     * en:
     * jpn: Adjustableのtrim設定をすることでbeforeValidateの段階でフィールドにtrim()を適用できる
     */
    public function testSaveTrim(){
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'title',
                                                      'trim' => true,
                                                      'encoding' => 'UTF-8'),
                                                );
        $data = array('FuzzyPost' => array('title' => ' Title3 ',
                                           'title_mb' => 'タイトル３',
                                           'body' => 'Save OK'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['title'], 'Title3');

        // en:
        // jpn: trimのパラメータにtrueでない文字列を設定することでtrim()の第2引数($charlist)を設定することが可能
        $this->FuzzyPost->convertFields = array(
                                                array('field' => 'title',
                                                      'trim' => 'Ti3',
                                                      'encoding' => 'UTF-8'),
                                                );
        $data = array('FuzzyPost' => array('title' => 'Title3',
                                           'title_mb' => 'タイトル３',
                                           'body' => 'Save OK'));
        $result = $this->FuzzyPost->save($data);
        $this->assertType('array', $result);

        $id = $this->FuzzyPost->getLastInsertId();
        $result = $this->FuzzyPost->findById($id);
        $this->assertIdentical($result['FuzzyPost']['title'], 'tle');
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
}