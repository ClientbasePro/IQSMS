<?php

  // интеграция CRM Clientbase и СМС-сервиса IQSMS 
  // https://ClientbasePro.ru
  // https://iqsms.ru/api/api_rest-php/
  
require_once 'common.php'; 

    // отправка СМС с текстом $text на телефон $phone от имени отправителя $sender, доп.признак $needSave сохраняет СМС в лог, доп.массив $data для сохранения в лог
function IQSMS_SendSMS($phone, $text, $sender, $needSave=1, $data='') {
    // проверка и форматирование входных данных
  $phone = SetNumber($phone);
  $text = form_input($text);
  if (!$sender) $sender = IQSMS_DEFAULT_NAME;
  if (!$phone || !$text) return false;
    // подготовка данных для запроса к серверу IQSMS
  $phone_ = rawurlencode($phone);
  $text_ = rawurlencode($text);
  $sender = rawurlencode($sender);
  $url = IQSMS_URL.'send?phone='.$phone_.'&text='.$text_.'&sender='.$sender.'&login='.IQSMS_API_LOGIN.'&password='.IQSMS_API_PASSWORD;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $tmp = curl_exec($curl);    
  curl_close($curl);
    // сохранить в лог?
  if ($needSave && SMSLOG_TABLE) {
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
  if (!$messageId) return 'no messageId';
  $url = IQSMS_URL.'status?&login='.IQSMS_API_LOGIN.'&password='.IQSMS_API_PASSWORD.'&id='.$messageId;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $tmp = curl_exec($curl);    
  curl_close($curl);
  return $tmp;
}

    // возвращает список доступных подписей в СМС
function IQSMS_GetSenders() {
  $url = IQSMS_URL.'senders?&login='.IQSMS_API_LOGIN.'&password='.IQSMS_API_PASSWORD;
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $tmp = curl_exec($curl);    
  curl_close($curl);
  return $tmp;
}

?>