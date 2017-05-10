<?php
define('DEBUG', false);
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
	$data[] = "Фамилия: " . $post['f'];
	$data[] = "Имя: " . $post['i'];
	$data[] = "Отчество: " . $post['o'];
	$data[] = "Телефон: " . $post['tel'];
	$data[] = "Почта: " . $post['email'];
	$data[] = "Город: " . $post['city'];
	$data[] = "Специальность: " . $post['profession'];
	$data[] = "Место работы: " . $post['job'];
	$data[] = "Уже посещал ICTPS ранее: " . ($post['regular'] ? 'ДА' : 'нет');
	$data[] = "Ординатор/интерн/аспирант: " . ($post['student'] ? 'ДА' : 'нет');
	$data[] = "Член РОПРЭХ: " . ($post['spras'] ? 'ДА' : 'нет');
	$data[] = "Пойдёт на гала-ужин: " . ($post['dinner'] ? 'ДА' : 'нет');
	$data[] = "Нужна помощь с проживанием: " . ($post['appointment'] ? 'ДА' : 'нет');
	$data[] = "Цена: " . $post['price'] . ' руб.';
	$data[] = "IP: " . $ip;
	$data[] = "---------------------------------------";
	$data[] = "";

	sendEmail('d.medentsov@bioconcept.ru', 'Регистрация на ICTPS (' . $post['f'] . ')', $data );

	file_put_contents( dirname(__FILE__) .'/registration.txt', implode("\r\n", $data), FILE_APPEND);

	$myCurl = curl_init();
	curl_setopt_array($myCurl, array(
		CURLOPT_URL => 'http://www.ictps.ru/events/new_order.php',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => http_build_query($post)
	));
	$response = curl_exec($myCurl);
	curl_close($myCurl);

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
