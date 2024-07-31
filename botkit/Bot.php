<?php
// Класс бота. Отвечает за вызов событий драйверов

namespace BotKit;

use BotKit\Models\Events\IEvent;
use BotKit\Drivers\IDriver;
use BotKit\Entities\{User as UserEntity, Platform};
use BotKit\Models\User as UserModel;
use BotKit\Enums\State;

class Bot {

    // Событие которое обрабатывает бот
    private static IEvent $event;

    // Загружен ли драйвер
    private static bool $driver_loaded = false;

    // Драйвер, который будет обрабатывать запрос
    private static IDriver $driver;

    // Загружает драйвер
    // Драйвер определяет будет ли он обрабатывать запрос и если ответ
    // положительный, драйвер становится загруженным.
    public static function loadDriver(IDriver $driver) {
        if (self::$driver_loaded == true) {
            // Драйвер уже выбран
            return;
        }

        if ($driver->forThis()) {
            self::$driver_loaded = true;
            self::$driver = $driver;
        }
    }

    // Убеждается в том, что драйвер для бота загружен
    public static function onLoadingFinished() {
        // Выбросить исключение если ни один драйвер не согласился обработать
        // запрос
        if (self::$driver_loaded == false) {
            throw new \Exception("Bot has no loaded drivers");
        }
        self::$driver->onSelected();

        // "- Драйвер, какой у пользователя ID на платформе?"
        $user_platform_id = self::$driver->getUserIdOnPlatform();
        // "- А какая у тебя вообще платформа?"
        $driver_platform = self::$driver->getPlatformDomain();

        // Получение объекта сущности пользователя
        $em = Database::getEM();
        $query = $em->createQuery(
            'SELECT user, platform FROM '. UserEntity::class .' user '.
            'JOIN user.platform platform '.
            'WHERE platform.domain=:platformDomain '.
            'AND user.id_on_platform=:id_on_platform');
        $query->setParameters([
            'platformDomain' => $driver_platform,
            'id_on_platform'=> $user_platform_id
        ]);
        $user_entity = $query->getResult()[0];

        if ($user_entity === null) {
            // Нет пользователя, создаём
            $platform_query = $em->createQuery('SELECT platform FROM '.
            Platform::class .' platform WHERE platform.domain=:platformDomain');
            $platform_query->setParameters([
                'platformDomain' => $driver_platform
            ]);
            $platform = $platform_query->getResult()[0];
            
            $user_entity = new UserEntity();
            $user_entity->setIdOnPlatform($user_platform_id);
            $user_entity->setPlatform($platform);
            $user_entity->setState(State::FirstInteraction);

            $em->persist($user_entity);
        }
        
        $user_model = new UserModel($user_entity, $user_platform_id);

        // "- Я нашёл пользователя по тем данным, которые ты мне дал, можешь
        // теперь создать объект события? Я его потом сверю с правилами из
        // routing.php"
        self::$event = self::$driver->getEvent($user_model);
    }

    // Общая процедура обработки запроса
    // $callback - то что будет выполняться
    // $params - доп. параметры для $callback
    private static function processRequest(string $callback, array $params) : void {
        self::$driver->onProcessStart();

        // Создание объекта контроллера
        // TODO: проверка ошибок формата $callback
        list($class_name, $method_name) = explode('@', $callback);
        $class_name = 'BotKit\\Controllers\\' . $class_name;
        $controller = new $class_name;

        // Инициализация объекта
        $controller->init(self::$event, self::$driver);

        // Вызов метода объекта с параметрами
        call_user_func_array([$controller, $method_name], $params);

        // Сохранение изменений
        // Необходимо, если метод контроллера меняет состояние пользователя
        // или что-либо ещё. Не дописывать же эти две строки после почти каждых
        // методов контроллеров.
        $em = Database::getEM();
        $em->flush();

        // Завершение работы
        self::$driver->onProcessEnd();
        exit();
    }

    // Подключает обработчик события
    //~ public function on(
        //~ string $event_classname,
        //~ callable $check,
        //~ callable $callback
    //~ ) : void
    //~ {

        //~ if (!is_a(self::$event, $event_classname, true)) {
            //~ // Если подключается обработчик события класса $event_classname,
            //~ // а в этом запросе обрабатывается событие другого класса, то
            //~ // и привязывать обработчик нет необходимости
            //~ return;
        //~ }

        //~ // Проверка условия события
        //~ $check_params = [
            //~ 'e' => self::$event,
            //~ 'u' => self::$event->getUser(),
            //~ 'driver' => self::$driver
        //~ ];

        //~ $result = call_user_func_array($check, $check_params);
        //~ if ($result == false) {
            //~ // Обрабатываемое событие - не для этого обработчика
            //~ return;
        //~ }
        //~ self::processRequest($callback, []);
    //~ }

    // Подключает обработчик события
    public static function onEvent(string $event_classname, string $callback) {
        if (!is_a(self::$event, $event_classname, true)) {
            // Событие, которое сейчас обрабатывается - это не событие,
            // которое проверяет эта функция. Поиск продолжается
            return;
        }
        self::processRequest($callback, []);
    }

    // Подключает обработчик команды
    // Команда должна быть только текстовым сообщением
    public function onCommand(string $template, callable $callback) {
        if (!is_a(self::$event, PlainMessageEvent::class, true)) {
            // Не текстовое сообщение
            return;
        }

        // Определяем что будет параметрами
		$pattern = '/^'.preg_replace(
			['/\//','/{(\w+)}/'],
			['\\\/', '(?<$1>.*)'],
			$template
		).'$/';


        if (!preg_match($pattern, self::$event->getText(), $named_groups)) {
            // Обрабатываемое событие - не для этого обработчика
            return;
		};

        // Оставляем только строковые ключи, т.к. в processRequest
        // $named_groups будут соединены со стандартными параметрами, которые
        // передаются только по названиям, не по позициям. Если в $named_groups
        // попадётся числовой ключ, позиционный аргумент передастся после
        // ключевого, что приведёт к ошибке
        $named_groups_filter = array_filter(
            $named_groups,
            function ($k) {
                return is_string($k);
            },
            ARRAY_FILTER_USE_KEY
        );

        // Вызов обработки с пойманными параметрами
        self::processRequest($callback, $named_groups_filter);
    }

    // Подключает обработчик обратного вызова
    public function onCallback(CallbackType $callbackType, callable $responseCallback) {
        if (!is_a(self::$event, CallbackEvent::class)) {
            // Не событие обратного вызова
            return;
        }

        if (self::$event->getCallbackType() != $callbackType) {
            // Этот тип обратного вызова не подходит
            return;
        }

        // Вызов обработки
        self::processRequest($responseCallback, []);
    }

    // Подключает обработчик текстового для состояния пользователя
    public function onPlainMessage(State $required_state, callable $callback) {
        if (!self::$event->getUser()->inState($required_state)) {
            // Требуемое состояние у пользователя не обнаружено
            return;
        }
        self::processRequest($callback, []);
    }

    // Все условия не прошли, вызываем план Б
    public function fallback(callable $callback) {
        self::processRequest($callback, []);
    }

    public static function getCurrentDriver() : IDriver {
        return self::$driver;
    }
}