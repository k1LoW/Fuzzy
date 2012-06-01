<?php

class DetectEncodingComponent extends Component {

    public $encoding;

    /**
     * __construct
     *
     * @param ComponentCollection $collection instance for the ComponentCollection
     * @param array $settings Settings to set to the component
     * @return void
     */
    public function __construct(ComponentCollection $collection, $settings = array()) {
        $this->controller = $collection->getController();
        parent::__construct($collection, $settings);
    }

    /**
     * startUp
     *
     * @param $controller
     * @return
     */
    public function startUp(Controller $controller) {
        // Set FuzzyForm
        $controller->helpers['Form'] = array('className' => 'Fuzzy.FuzzyForm');
        $this->request = $controller->request;
        $isPost = ($this->request->is('post') || $this->request->is('put'));
        if ($isPost) {
            $this->detectEncoding();
        }
    }

    /**
     * detectEncoding
     *
     * @see http://tanaka.sakura.ad.jp/archives/001071.html
     */
    public function detectEncoding(){
        $this->encoding = null;
        Configure::write('Fuzzy.encoding', $this->encoding);
        if (!empty($this->request->data['_Fuzzy']['detectstr'])) {
            $charsetList = array(
                                 "UTF-8"  => "%E6%96%87%E5%AD%97",
                                 "SJIS"   => "%95%B6%8E%9A",
                                 "EUC-JP" => "%CA%B8%BB%FA",
                                 "JIS"    => "%1B%24BJ8%3Bz%1B%28B",
                                 "BIG-5"  => "%A4%E5%A6r",
                                 "HZ"     => "%7E%7BNDWV%7E%7D",
                                 "EUC-KR" => "%D9%FE%ED%AE",
                                 "GB2312" => "%CE%C4%D7%D6",
                                 );
            foreach( $charsetList as $key => $val ){
                if( urlencode($this->request->data['_Fuzzy']['detectstr']) == $val ){
                    $this->encoding = $key;
                    Configure::write('Fuzzy.encoding', $this->encoding);
                    break;
                }
            }
        }
    }
}