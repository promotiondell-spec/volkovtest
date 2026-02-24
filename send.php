<?php
// send.php
header('Content-Type: application/json; charset=utf-8');

function json_fail($msg, $code = 400) {
  http_response_code($code);
  echo json_encode(["ok" => false, "error" => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_fail('Метод не поддерживается', 405);
}

// Простейшая защита от "пустых" запросов
$name   = trim($_POST['name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$source = trim($_POST['source'] ?? '');
$privacy = isset($_POST['privacy']) ? $_POST['privacy'] : null;

if ($name === '' || $email === '' || $phone === '') {
  json_fail('Заполните имя, email и телефон.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  json_fail('Некорректный email.');
}

if ($privacy === null) {
  json_fail('Нужно согласиться с политикой конфиденциальности.');
}

// Нормализация и ограничение длины
$name  = mb_substr($name, 0, 120);
$email = mb_substr($email, 0, 120);
$phone = mb_substr($phone, 0, 60);
$source = mb_substr($source, 0, 120);

// Куда отправлять
$to = "ivan@wmolf.ru";

// Тема и тело письма
$subject = "Заявка с лендинга RIGEL";
$body =
"Новая заявка:\n\n" .
"Имя: {$name}\n" .
"Email: {$email}\n" .
"Телефон: {$phone}\n" .
"Источник: {$source}\n" .
"Дата: " . date('Y-m-d H:i:s') . "\n" .
"IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

// ВАЖНО: From лучше делать доменным адресом вашего сайта, иначе письмо может не дойти.
// Замените no-reply@your-domain.ru на реальный домен.
$from = "no-reply@your-domain.ru";

$headers = [];
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-Type: text/plain; charset=UTF-8";
$headers[] = "From: {$from}";
$headers[] = "Reply-To: {$email}";

$ok = mail($to, "=?UTF-8?B?".base64_encode($subject)."?=", $body, implode("\r\n", $headers));

if (!$ok) {
  json_fail('Сервер не смог отправить письмо. Проверьте настройки почты на хостинге.', 500);
}

echo json_encode(["ok" => true], JSON_UNESCAPED_UNICODE);
