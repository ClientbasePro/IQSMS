<?php

  // интеграция CRM Clientbase и СМС-сервиса IQSMS 
  // https://ClientbasePro.ru
  // https://iqsms.ru/api/api_rest-php/
  
require_once 'common.php'; 

  // отправка СМС с текстом $text на телефон $phone от имени отправителя $sender, доп.признак $needSave сохраняет СМС в лог, доп.массив $data для сохранения в лог
function IQSMS_SendSMS($phone='', $text='', $sender='', $needSave=1, $data=[]) {
    // проверка и форматирование входных данных
  if (!defined('IQSMS_URL') || !defined('IQSMS_API_LOGIN') || !defined('IQSMS_API_PASSWORD')) return false;
  if ('/'==substr(IQSMS_URL,-1)) $IQSMS_URL = substr(IQSMS_URL,0,-1);
  else $IQSMS_URL = IQSMS_URL;
  $phone = SetNumber($phone);
  $text = form_input($text);
  if (!$sender && defined('IQSMS_DEFAULT_NAME')) $sender = IQSMS_DEFAULT_NAME;
  if (!$phone || !$text || !$sender) return false;
    // подготовка данных для запроса к серверу IQSMS
  $phone_ = rawurlencode($phone);
  $text_ = rawurlencode($text);
  $sender = rawurlencode($sender);
  $url = $IQSMS_URL.'/send?phone='.$phone_.'&text='.$text_.'&sender='.$sender.'&login='.IQSMS_API_LOGIN.'&password='.IQSMS_API_PASSWORD;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $tmp = curl_exec($curl);    
  curl_close($curl);
    // сохранить в лог?
  if ($needSave && defined('SMSLOG_TABLE') && defined('SMSLOG_FIELD_UID') && defined('SMSLOG_FIELD_PHONE') && defined('SMSLOG_FIELD_TEXT')) {
	$sms = explode(";", $tmp);
	$ins['f'.SMSLOG_FIELD_UID] = $sms[1];
	if ($ins['f'.SMSLOG_FIELD_UID] && 'accepted'==$sms[0]) {
  	  $ins['f'.SMSLOG_FIELD_PHONE] = $phone;
      $ins['f'.SMSLOG_FIELD_TEXT] = $text;
	  if ($data) foreach ($data as $fieldId=>$value) $ins[$fieldId] = $value;
      $tmp .= ';'.data_insert(SMSLOG_TABLE, EVENTS_ENABLE, $ins);
    }
  }  
  return $tmp;
}

  // возвращает статус сообщения с $messageId
function IQSMS_GetSMSStatus($messageId='') {
  if (!defined('IQSMS_URL') || !defined('IQSMS_API_LOGIN') || !defined('IQSMS_API_PASSWORD') || !$messageId) return false;
  if ('/'==substr(IQSMS_URL,-1)) $IQSMS_URL = substr(IQSMS_URL,0,-1);
  else $IQSMS_URL = IQSMS_URL;
  $url = $IQSMS_URL.'/status?&login='.IQSMS_API_LOGIN.'&password='.IQSMS_API_PASSWORD.'&id='.$messageId;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $tmp = curl_exec($curl);    
  curl_close($curl);
  return $tmp;
}

  // возвращает список доступных подписей в СМС
function IQSMS_GetSenders() {
  if (!defined('IQSMS_URL') || !defined('IQSMS_API_LOGIN') || !defined('IQSMS_API_PASSWORD')) return false;
  if ('/'==substr(IQSMS_URL,-1)) $IQSMS_URL = substr(IQSMS_URL,0,-1);
  else $IQSMS_URL = IQSMS_URL;
  $url = $IQSMS_URL.'/senders?&login='.IQSMS_API_LOGIN.'&password='.IQSMS_API_PASSWORD;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $tmp = curl_exec($curl);    
  curl_close($curl);
  return $tmp;
}
