<?php
// Класс вложения в сообщения
// Например: изображение, видео, клавиатура, кнопка клавиатуры
// Не все методы обязательны для реализации, т.к. например клавиатура
// не имеет смысл создаваться на основании уже загруженной. В таком случае
// следует выбрасывать ошибку

namespace BotKit\Models\Attachments;

interface IAttachment {

    // Создаёт вложение на основании файла с диска
    public static function createFromDisk(string $file_path) : IAttachment;

    // Создаёт вложение на основании существующего загруженного вложения
    // Например, бот загрузил фото на сервер telegram, он вернул его id,
    // этот id может использоваться в этой функции
    public static function createFromUploaded(
        string $id_on_platform) : IAttachment;

    // Создаёт вложение на основании URL
    public static function createFromUrl(string $url) : IAttachment;
}