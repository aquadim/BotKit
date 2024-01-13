<?php
// Драйвер ботов для ВКонтакте

namespace BotKit\Drivers;

use BotKit\User;
use BotKit\EventData;
use BotKit\Message;
use BotKit\FsmState;

class VkBotDriver implements Driver {
	#region События этого драйвера
	
	#endregion

	// Токен доступа
	private string $access_token;

	public function __construct($access_token) {
		$this->access_token = $access_token;
	}

	public function willHandleRequest() : bool {
		// Событие от ВКонтакте содержит type, object и group_id
		$data = json_decode(file_get_contents("php://input"));
		return property_exists($data, 'type') && property_exists($data, 'object') && property_exists($data, 'group_id');
	}

	public function getEventData() : EventData {
		$data = json_decode(file_get_contents("php://input"));
		$text = null;
		$event_type = Driver::FALLBACK;
		
		switch ($data->type) {
			case "message_new":
				$event_type = Driver::MSG_PLAIN;
				$text = $data->object->message->text;
				break;

			case "message_event":
				$event_type = Driver::MSG_CALLBACK;
				$callback_type = $data->object->payload->type;
				break;

			case "confirmation":
				$event_type = Driver::CONFIRMATION;
				break;

			default:
				break;
		}
		return new EventData($text, $data, $event_type);
	}

	// Возвращает объект пользователя
	public function getUser(EventData $event_data) : User {
		$payload = $event_data->getPayload();

		$vkid = null;
		$state = FsmState::HelloWorld;

		switch ($payload->event_type) {
			case "message_new":
				$vkid = $payload->object->message->from_id;
				break;
			case "message_event":
				$vkid = $payload->data->object->peer_id;
				break;
			default:
				break;
		}

		return new User($vkid, $state);
	}
	
	public function sendMessage(User $user, Message $message) : Message {
		$params = array(
			"peer_id" => $user->platform_id,
			"message" => $message->text,
			"random_id" => 0,
			"access_token" => $this->access_token,
			"v" => "5.131"
		);
		$response = file_get_contents("https://api.vk.com/method/messages.send?".http_build_query($params));
		$data = json_decode($response);
		$message->id = $data->response->id;
		return $message;
	}

	public function onStart() : void {
		// Закрываем соединение для того чтобы скрипт мог работать больше чем 10 секунд
		// Скрипт должен уметь работать больше чем 10 секунд потому что если vk не получил "ok"
		// за 10 секунд от сервера, он пришлёт запрос ещё раз. На самом деле сервер обрабатывал первый
		// запрос, и когда он его закончил, он ответил бы "ok", но второй запрос уже прислался...
		// Так будет происходить 5 раз перед тем как вк не сдастся и не прекратит присылать новые запросы
		// https://ru.stackoverflow.com/q/893864/418543
		ob_end_clean();
		header("Connection: close");
		ignore_user_abort(true);
		ob_start();
		echo "ok";
		$size = ob_get_length();
		header("Content-Length: ".$size);
		ob_end_flush();
		flush();
	}

	public function letHandling() : bool {
		return true;
	}
}