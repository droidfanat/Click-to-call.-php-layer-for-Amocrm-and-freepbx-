<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/MyAmo.php';
require_once __DIR__ . '/src/ModelRecords.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define('USER_LOGIN', 'admin@***.com');
define('USER_HASH', '7dde7a93bd13877****');
define('SUBDOMAIN', 'domain');

//error_reporting(E_ALL);
//ini_set('display_errors', 1);


abstract class card {

    public $card_id;
    public $usr_id;
    public $did;
    public $link;
    public $duration;
    public $tupe;
    public $status;
    public $result;

}

class call extends card {

    public $logger;

    function __construct($logger) {
        $this->logger=$logger;
        $this->parseCard();
        $this->status();
        $this->addRecord();
    }

    function parseCard() {
        $this->card_id = intval(end(explode('/', $_GET['card'])));
        if (stristr($_GET['card'], 'companies')) {
            $this->tupe = src\Models\ModelRecords::TYPE_COMPANY;
        } elseif (stristr($_GET['card'], 'leads')) {
            $this->tupe = src\Models\ModelRecords::TYPE_LEAD;
        } elseif (stristr($_GET['card'], 'contacts')) {
            $this->tupe = src\Models\ModelRecords::TYPE_CONTACT;
        }
    }

    function status() {
        switch ($_GET['status']) {
            case 'ANSWER':
                $this->status = '4';
                $this->result = "Был Разговор";
                break;
            case 'BUSY':
                $this->status = '7';
                $this->result = "Занято";
                break;
            case 'NOANSWER':
                $this->status = '6';
                $this->result = "Без ответа";
                break;
            default:
                break;
        }
    }

    function addRecord() {

        $record = array(
            'id' => $this->card_id,
            'user_id' => $_GET['user_id'],
            'phone' => $_GET['phone'],
            'link' => 'https://lfs.crm.vrdp.online/RECORDINGS/MP3/' . $_GET['rec'],
            'duration' => intval($_GET['dial']),
            'type' => $this->tupe,
            'result' => $this->result,
            'status' => $this->status,
        );
        $log = '';
        foreach ($record as $key => $value) {
            $log .= '  ' . $key . ' =>  ' . $value;
        }
        $this->logger->warning($log);
        $AmoRegust = new src\Models\ModelRecords(SUBDOMAIN, USER_LOGIN, USER_HASH);
        $this->logger->warning('Response '.$AmoRegust->query($record));
    }

}

$logger = new Logger('call-card');
$logger->pushHandler(new StreamHandler('./call-api.log', Logger::WARNING));
$call = new call($logger);
