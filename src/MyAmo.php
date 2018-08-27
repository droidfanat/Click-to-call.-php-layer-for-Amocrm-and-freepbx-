<?php

namespace src\Models;

/**
 * Description of newPHPClass
 *
 * @author admin2
 */
abstract class MyAmo {

    public function __construct() {
        $this->subdomain = 'domen'; #Наш аккаунт - поддомен
        $this->autch();
    }

    abstract public function query($query);

    public function autch() {
        $user = array(
            'USER_LOGIN' => 'admin@...', #Ваш логин (электронная почта)
            'USER_HASH' => '7dde7a93bd13877c...' #Хэш для доступа к API (смотрите в профиле пользователя)
        );
        $link = 'https://' . $this->subdomain . '.amocrm.ru/private/api/auth.php?type=json';
        $this->amocurl($link, $user);
    }

    public function amocurl($link, $query) {
        print_r(json_encode($query));
       
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($query));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        
        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
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
