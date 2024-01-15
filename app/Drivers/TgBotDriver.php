<?php
// Драйвер ботов для Телеграм

namespace BotKit\Drivers;

use BotKit\Keyboards\Keyboard;

use BotKit\User;
use BotKit\EventData;
use BotKit\Message;
use BotKit\FsmState;

class TgBotDriver implements Driver {
	#region События этого драйвера
	
	#endregion

	// Токен бота
	private string $bot_token;

	public function __construct($bot_token) {
		$this->bot_token = $bot_token;
	}

	public function willHandleRequest() : bool {
		// Событие от Телеграм содержит update_id
		$data = json_decode(file_get_contents("php://input"));
		return property_exists($data, 'update_id');
	}

	public function getEventData() : EventData {
		$data = json_decode(file_get_contents("php://input"));
		$text = null;
		$event_type = Driver::FALLBACK;

		if (property_exists($data, 'message')) {
			if (property_exists($data->message, 'text')) {
				$event_type = Driver::MSG_PLAIN;
				$text = $data->message->text;
			}
		}
		
		return new EventData($text, $data, $event_type);
	}

	// Возвращает объект пользователя
	public function getUser(EventData $event_data) : User {
		$payload = $event_data->getPayload();

		$tgid = null;
		$state = FsmState::HelloWorld;
		$first_name = null;
		$last_name = null;

		switch ($event_data->getEventType()) {
			case Driver::MSG_PLAIN:
				$tgid = $payload->message->from->id;
				$first_name = $payload->message->from->first_name;
				$last_name = $payload->message->from->last_name;
				break;
			default:
				break;
		}

		return new User($tgid, $first_name, $last_name, $state);
	}
	
	public function sendMessage(User $user, Message $message) : Message {
		$url = "https://api.telegram.org/bot".$this->bot_token."/sendMessage?";
		$params = array(
			"chat_id" => $user->getPlatformId(),
			"text" => $message->getText(),
			"reply_markup" => $this->getKeyboard($message->getKeyboard())
		);
		$response = file_get_contents($url.http_build_query($params));
		$data = json_decode($response);

		if (!$data->ok) {
			throw new \Exception("There was an error sending message");
		}

		$message->id = $data->result->message_id;
		
		return $message;
	}

	public function getKeyboard($kb) : string {
		if ($kb === null) {
			return '';
		}

		$kb_class = get_class($kb);
		if (is_a($kb_class, 'BotKit\Keyboards\Keyboard', true)) {
			// Обычная кнопка

			$layout = [];
			foreach ($kb->layout as $row) {
				$layoutrow = [];
				foreach ($row as $item) {
					$button = $item;
					$layoutrow[] = $button;
				}
				$layout[] = $layoutrow;
			}
			
			$params = [
				"keyboard" => $layout,
				"resize_keyboard" => isset($kb->tg_resize) ? $kb->tg_resize : true,
				"one_time_keyboard" => isset($kb->tg_onetime) ? $kb->tg_onetime : true,
				"selective" => isset($kb->tg_selective) ? $kb->tg_selective : true
			];
		}

		return json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
	}

	public function onStart() : void {
	}

	public function letHandling() : bool {
		return true;
	}
}