<?php





abstract class Amo {

    public function __construct() {
        $this->subdomain = 'example'; 

    abstract public function query($query);

    public function autch() {
        $user = array(
            'USER_LOGIN' => 'admin@....', 
            'USER_HASH' => '7dde7a93bd13877c73bbe89f....' 
        );
        $link = 'https://' . $this->subdomain . '.amocrm.ru/private/api/auth.php?type=json';
        $this->amocurl($link, $user);
    }

    public function amocurl($link, $query, $post = true) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        if ($post) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($query));
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl); 

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $code = (int) $code;
        $errors = array(
            301 => 'Moved permanently',
            400 => 'Bad request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not found',
            500 => 'Internal server error',
            502 => 'Bad gateway',
            503 => 'Service unavailable'
        );
        try {
            if ($code != 200 && $code != 204)
                throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error', (int) $code);
            return json_decode($out);
        } catch (Exception $E) {
            die('Ошибка: ' . $E->getMessage() . PHP_EOL . 'Код ошибки: ' . $E->getCode());
            return array();
        }
    }

}

class AmoAddCallRecords extends Amo {

    public function query($record) {

        print_r($record);
        $notes['add'] = array(
            array(
                'element_id' => $record['id'],
                'element_type' => 2,
                'note_type' => 11,
                'params' => array(
                    'UNIQ' => '676sdfs7fsdd',
                    'LINK' => $record['link'],
                    'PHONE' => $record['phone'],
                    'DURATION' => $record['duration'],
                    'SRC' => 'asterisk',
                    'call_status' => '4', 
                    'call_result' => 'Поговорили' 
                )
            )
        );

        $link = 'https://' . $this->subdomain . '.amocrm.ru/api/v2/notes';
        print_r($this->amocurl($link, $notes));
    }

}

class AmoCrmGet extends amo {

    public function query($query) {

        $search_queryes = array(
            'contacts',
            'leads',
        );

    $cards = array();
        foreach ($search_queryes as $queryes) {
            $link = 'https://' . $this->subdomain . '.amocrm.ru/api/v2/' . $queryes . '/?query=' . $query . '';
            print_r($link);
            $response = $this->amocurl($link, '', False);
            if ($response) {
                foreach ($response->_embedded->items as $item) {
                    $cards[] = $item;
                }
            }
        }
      
        
        return $cards;   
        
    }

}





class Asterisk {

    public $phone='79260624913';
    public $link='www.testweb.ru/test_call.mp3';
    public $duration=100;
            
    
    function __construct() {
        $AmoCrmGet = new AmoCrmGet();
        $AmoAddCallRecords = new AmoAddCallRecords();
        
        
        foreach ($AmoCrmGet->query($this->phone) as $card) {
           $record= array(
             'id'=>$card->id,
             'phone'=> $this->phone,
             'link'=> $this->link,
             'duration'=> $this->duration  
           ); 
           $AmoAddCallRecords->query($record);
        }


        
    }

}
//Run Test
new Asterisk();