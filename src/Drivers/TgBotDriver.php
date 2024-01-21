<?php
// Драйвер ботов для Телеграм

namespace BotKit\Drivers;

use BotKit\Keyboards\Keyboard;
use BotKit\Common\User;
use BotKit\Common\EventData;
use BotKit\Common\Message;
use BotKit\Enums\FsmState;
use BotKit\Enums\ImageAttachmentType;
use BotKit\Models;

class TgBotDriver implements Driver {
	#region События этого драйвера
	
	#endregion

	// Токен бота
	private string $bot_token;

	// Базовый адрес API
	private string $api_base;

	// Название платформы бота
	private string $platform_name = "telegram.org";

	public function __construct($bot_token) {
		$this->bot_token = $bot_token;
		$this->api_base = "https://api.telegram.org/bot".$this->bot_token;
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

		// Поиск пользователя в БД
		$db_object = Models\User::where([
			['id_on_platform', '=', $tgid],
			['platform', '=', $this->platform_name]
		]);

		return new User($tgid, $first_name, $last_name, $state, null);
	}
	
	public function sendMessage(User $user, Message $message) : Message {
		if ($message->hasImages()) {
			// Если сообщение содержит изображения, это совсем другой метод
			// TODO: добавить поддержку отсылки нескольких фотографий

			// Интерпретация свойства $value в изображении
			$image = $message->getImages()[0];
			$value = $image->getValue();
			
			switch ($image->getType()) {
				case ImageAttachmentType::FromFile:
					$photo_object = new \CURLFile($value);
					break;
				case ImageAttachmentType::FromUrl:
				case ImageAttachmentType::FromExisting:
					$photo_object = $value;
					break;
				default:
					throw new \Exception("Not implemented");
			}

			// Отправка изображения
			$params = [
				"chat_id" => $user->getPlatformId(),
				"caption" => $message->getText(),
				"photo" => $photo_object
			];
			$ch = curl_init($this->api_base."/sendPhoto");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$response = curl_exec($ch);

			var_dump($response);

			// TODO: сохранять ID изображения после его отправки
			
			return $message;
		}

		$url = $this->api_base."/sendMessage?";
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
			// Обычная клавиатура

			$layout = [];
			foreach ($kb->layout as $row) {
				$layoutrow = [];
				foreach ($row as $item) {
					$button = $item->getText();
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