<?php
class FuzzyPostFixture extends CakeTestFixture {
    var $name = 'FuzzyPost';

    var $fields = array(
        'id' => array('type' => 'integer', 'null' => false, 'default' => NULL, 'length' => 20, 'key' => 'primary'),
        'title' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'title_mb' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'body' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'tel_no1' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'tel_no2' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'tel_no3' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'zip1' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'zip2' => array('type' => 'text', 'null' => true, 'default' => NULL, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
        'created' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
        'modified' => array('type' => 'datetime', 'null' => true, 'default' => NULL),
    );

    var $records = array(
                         array(
                               'id' => 1,
                               'title' => 'Title',
                               'title_mb' => 'タイトル',
                               'body' => 'Fuzzy.FuzzyInput Test',
                               'tel_no1' => '092',
                               'tel_no2' => '1234',
                               'tel_no3' => '5678',
                               'zip1' => '000',
                               'zip2' => '0000',
                               'created' => '2011-08-23 17:44:58',
                               'modified' => '2011-08-23 12:05:02'
                               ),
                         array(
                               'id' => 2,
                               'title' => 'Title2',
                               'title_mb' => 'タイトル２',
                               'body' => 'Fuzzy.FuzzyInput Test',
                               'tel_no1' => '092',
                               'tel_no2' => '1234',
                               'tel_no3' => '5678',
                               'zip1' => '000',
                               'zip2' => '0000',
                               'created' => '2011-08-23 17:44:58',
                               'modified' => '2011-08-23 12:05:02'
                               ),
                         );
}