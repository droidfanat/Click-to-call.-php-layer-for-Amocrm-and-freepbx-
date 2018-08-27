<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace src\Models;

/**
 * Description of modelRecords
 *
 * @author admin2
 */
class ModelRecords extends MyAmo {
    
     protected $types = [
        self::DEAL_CREATED => 'Сделка создана',
        self::CONTACT_CREATED => 'Контакт создан',
        self::DEAL_STATUS_CHANGED => 'Статус сделки изменен',
        self::COMMON => 'Обычное примечание',
        self::ATTACHMENT => 'Файл',
        self::CALL => 'Звонок приходящий от iPhone-приложений',
        self::EMAIL_MESSAGE => 'Письмо',
        self::EMAIL_ATTACHMENT => 'Письмо с файлом',
        self::CALL_IN => 'Входящий звонок',
        self::CALL_OUT => 'Исходящий звонок',
        self::COMPANY_CREATED => 'Компания создана',
        self::TASK_RESULT => 'Результат по задаче',
        self::SYSTEM => 'Системное сообщение',
        self::SMS_IN => 'Входящее смс',
        self::SMS_OUT => 'Исходящее смс',
    ];

    const DEAL_CREATED = 1;
    const CONTACT_CREATED = 2;
    const DEAL_STATUS_CHANGED = 3;
    const COMMON = 4;
    const ATTACHMENT = 5;
    const CALL = 6;
    const EMAIL_MESSAGE = 7;
    const EMAIL_ATTACHMENT = 8;
    const CALL_IN = 10;
    const CALL_OUT = 11;
    const COMPANY_CREATED = 12;
    const TASK_RESULT = 13;
    const SYSTEM = 25;
    const SMS_IN = 102;
    const SMS_OUT = 103;

    /**
     * @const int Типа задачи Контакт
     */
    const TYPE_CONTACT = 1;

    /**
     * @const int Типа задачи Сделка
     */
    const TYPE_LEAD = 2;

    /** @const int Типа задачи Компания */
    const TYPE_COMPANY = 3;

    /** @const int Типа задачи Задача */
    const TYPE_TASK = 4;

    /** @const int Типа задачи Покупатель */
    const TYPE_CUSTOMER = 12;   
    
    
  public function query($record) {
        
        $notes['add'] = array(
       array(
       'element_id' => $record['id'],
       'element_type' =>$record['type'] ,
       'account_id'=>$record['user_id'],    
       'responsible_user_id'=>$record['user_id'],    
       'note_type' => 11,
       'params' => array(
        'UNIQ' =>'676sdfs7fsdf',
        'LINK' => $record['link'],
        'PHONE' => $record['phone'],
        'DURATION' => $record['duration'],
        'SRC' => 'asterisk',
        'call_status' => $record['status'], //статус
        'call_result' => $record['result'] //результат (необязательно)
      )
   )
);

        $link = 'https://' . $this->subdomain . '.amocrm.ru/api/v2/notes/';
        print_r($this->amocurl($link, $notes));
    }
}
