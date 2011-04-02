<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Kernel
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */


/**
 * Класс обработки ошибок
 *
 */
class Abs_Error {
    /**
     * Управление ошибками
     *
     * @param exception $exception Перехватываемое исключение
     */
    public static function catchException(Exception $exception) {

        // Получение текста ошибки
        $message = $exception->getMessage();
        // Получение трейса ошибки как строки
        $trace = $exception->getTraceAsString();
        $str = 'ERROR: ' . $message . "\n" . $trace;

        // Если включен режим отладки отображаем сообщение о ошибке на экран
        if(ABS_Kernel::isDebug()) {
            echo($str);
        }
        // Иначе выводим сообщение об ошибке
        else {
            // Здесь может происходить логирование ошибки, уведомление вебмастера и т д
            die('Ошибка в работе, пожалуйста повторите свой запрос позже!');
        }
    }
}
