<?php

class asterisk {

    public $prefix;
    public $host;
    public $port;
    public $login;
    public $key;

    public function __construct($login, $secret) {
        $this->port = 8088;
        $this->prefix = '/asterisk/';
        $this->host = 'localhost';
        $this->context = 'amo';
        $this->priority = 2;
        $this->login = $login;
        $this->key = $secret;

        $resp = $this->ajam_req(array(
            'Action' => 'Login',
            'username' => $this->login,
            'secret' => $this->key,
        ));
    }

    public function call($from, $to, $as, $var) { // originate a call
        $params = array(
            'action' => 'Originate',
            'channel' => 'SIP/' . intval($from),
            'Exten' => strval($to),
            'Context' => $this->context,
            'priority' => $this->priority,
            'Variable' => $var,
            'Callerid' => '"' . strval($as) . '" <' . intval($from) . '>',
            'Async' => 'Yes',
        );
        $resp = $this->ajam_req($params);
        return $resp;
    }

    private function ajam_req($params) {
        static $cookie;
        // EveryRequest Ajam sends back a cookir, needed for auth handling
        if ($cookie === NULL)
            $cookie = '';
        // make req. and store cookie
        list($body, $cookie) = $this->rq($this->prefix . 'rawman?' . http_build_query($params), $cookie);
        // parse an answer
        return $this->rawman_parse($body);
    }

    private function rq($url, $cookie = '') {
        // get RAW data
        $r = $this->_rq($url, $cookie);
        // divide in 2 parts
        list($headersRaw, $body) = explode("\r\n\r\n", $r, 2);
        // parse headers
        $headersRaw = explode("\r\n", $headersRaw);
        $headers = array();
        foreach ($headersRaw as $h) {
            if (strpos($h, ':') === false)
                continue;
            list($hname, $hv) = explode(":", $h, 2);
            $headers[strtolower(trim($hname))] = trim($hv);
        }
        // fetch cookie
        if (!empty($headers['set-cookie'])) {
            $listcookies = explode(';', $headers['set-cookie']);
            foreach ($listcookies as $c) {
                list($k, $v) = explode('=', trim($c), 2);
                if ($k == 'mansession_id')
                    $cookie = $v;
            }
        }

        return array($body, $cookie);
    }

    private function _rq($url, $cookie) {
        $errno = $errstr = "";
        $fp = fsockopen($this->host, $this->port, $errno, $errstr, 3);
        if (!$fp)
            return false;
        $out = "GET {$url} HTTP/1.1\r\n";
        $out .= "Host: " . $this->host . "\r\n";
        if (!empty($cookie))
            $out .= "Cookie: mansession_id={$cookie}\r\n";
        $out .= "Connection: Close\r\n\r\n";
        fwrite($fp, $out);
        $r = '';
        while (!feof($fp))
            $r .= fgets($fp);
        fclose($fp);
        return $r;
    }

    private function rawman_parse($lines) {
        $lines = explode("\n", $lines);
        $messages = array();
        $message = array();

        foreach ($lines as $l) {
            $l = trim($l);
            if (empty($l) and count($message) > 0) {
                $messages[] = $message;
                $message = array();
                continue;
            }
            if (empty($l))
                continue;
            if (strpos($l, ':') === false)
                continue;
            list($k, $v) = explode(':', $l);
            $k = strtolower(trim($k));
            $v = trim($v);
            if (!isset($message[$k]))
                $message[$k] = $v;
            elseif (!is_array($message[$k]))
                $message[$k] = array($message[$k], $v);
            else
                $message[$k][] = $v;
        }
        if (count($message) > 0)
            $messages[] = $message;
        return $messages;
    }

}






class myamo extends asterisk {

    public function __construct() {

        foreach (array('login', 'secret', 'action') as $k) {
            if (empty($_GET['_' . $k]))
                return;
            $$k = strval($_GET['_' . $k]);
        }
        parent::__construct($login, $secret);
        switch ($action) {
            case 'call':
                $this->callToCard();
                break;
            case 'status':
                $this->answer(array('status'=>'ok','action'=>null,'data'=>array()));
            default:
                return;
        }
    }

    // $referer='https://lfs.amocrm.ru/leads/detail/7355361';
    
    public function callToCard() {
       
        //$card_id = 'card=' . intval(end(explode('/', $_SERVER['HTTP_REFERER'])));
        $card_id = 'card=' . $_SERVER['HTTP_REFERER'];
        $crm_user_id='user_id=' .intval($_GET['from']);
        $vars = $card_id.','.$crm_user_id;
        $resp = $this->call($_GET['from'], $_GET['to'], $_GET['as'], $vars);

        if ($resp[0]['response'] !== 'Success') {
            $this->answer(array('status' => 'error', 'data' => $resp[0]));
        } else {
            $this->answer(array('status' => 'ok', 'action' => $action, 'data' => $resp[0]));
        }
    }

    public function answer($array, $no_callback = false) {
        header('Content-type: text/javascript;');
        if (!$no_callback)
            echo "asterisk_cb(" . json_encode($array) . ');';
        else
            echo json_encode($array);
        die();
    }

}

$myamo = new myamo();







