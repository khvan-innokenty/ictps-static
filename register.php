<?php
define('DEBUG', true);
include_once "PHPMailer/PHPMailerAutoload.php";

register();



/**
 * Зарегистрировать пользователя
 * Todo: реализовать запись данных пользователя в CRM
 */
function register() {
	$post = $_POST;
	$ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING);

	$post = filter_var_array($post, FILTER_SANITIZE_STRING);

	$tickets = array(
		array(
			'title' => "Конференция 4 дня",
			'id' => 1186
		),
		array(
			'title' => "Конференция 2 любых дня",
			'id' => 3168
		),
		array(
			'title' => "Конференция 9 – 10 июня для онкологов",
			'id' => 3175
		),
		array(
			'title' => "Диссекционный курс + конференция",
			'id' => 100
		),
		array(
			'title' => "Диссекционный курс без участия в конференции",
			'id' => 200
		)
	);
	$ticketId = intval($post['ticket']);
	$ticketTitle = "";
	for ($i = 0; $i<count($tickets); $i++) {
		if ($ticketId === intval($tickets[$i]['id']) ) {
			$ticketTitle = $tickets[$i]['title'];
			break;
		}
	}

	$data[] = "---------------------------------------";
	$data[] = date('d.m.Y H:i:s');
	$data[] = "РЕГИСТРАЦИЯ";
	$data[] = "Пакет участия: " . $ticketTitle . " (id=" . $ticketId . ")";
	$data[] = "ФИО: " . $post['fullname'];
	$data[] = "Телефон: " . $post['tel'];
	$data[] = "Почта: " . $post['email'];
	$data[] = "Город (по IP-адресу): " . $post['city'];
	$data[] = "Уже посещал ICTPS ранее: " . ($post['regular'] ? 'ДА' : 'нет');
	$data[] = "Ординатор/интерн/аспирант: " . ($post['student'] ? 'ДА' : 'нет');
	$data[] = "Член РОПРЭХ: " . ($post['spras'] ? 'ДА' : 'нет');
	$data[] = "Пойдёт на гала-ужин: " . ($post['dinner'] ? 'ДА' : 'нет');
	$data[] = "Нужна помощь с проживанием: " . ($post['appointment'] ? 'ДА' : 'нет');
	$data[] = "Цена: " . $post['price'] . ' руб.';
	$data[] = "IP: " . $ip;
	$data[] = "---------------------------------------";
	$data[] = "";

	sendEmail('d.medentsov@bioconcept.ru', 'Регистрация на ICTPS (' . $post['fullname'] . ')', $data );
	sendEmail('events@bioconcept.ru', 'Регистрация на ICTPS (' . $post['fullname'] . ')', $data );

	file_put_contents( dirname(__FILE__) .'/registration.txt', implode("\r\n", $data), FILE_APPEND);

	$response = '{"status":1, "msg":"Вы успешно зарегистрированы на мероприятие."}';

	echo $response;
}


/**
 * Отправить E-mail
 * @param $to
 * @param $subject
 * @param $template
 * @param $data
 * @return bool
 * @throws Exception
 * @throws phpmailerException
 */
function sendEmail( $to, $subject, $data ) {
	$mail = new PHPMailer;
	$mail->setLanguage('ru');

	if (DEBUG) return true;

	$mail->isSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = 'smtp.yandex.ru';
	$mail->Port = 465;
	$mail->SMTPSecure = 'ssl';
	$mail->CharSet = 'UTF-8';

	$mail->Username = 'no-reply@medesse.com';
	$mail->Password = "41k#wGaiiT";
	$mail->From = 'no-reply@medesse.com';
	$mail->FromName = 'MEDESSE';
	$mail->isHTML(false);

	$mail->Subject = $subject;
	$mail->addAddress( $to );

	$mail->Body = implode("\r\n", $data);

	return $mail->send();
}
