<?php
/**
 * ABS Library
 *
 * @category   ABS_Library
 * @package    Function
 * @copyright  Copyright (c) 2008 Shvakin V. (http://a1p2m3.googlepages.com/)
 * @version    2.0
 */

class Abs_Function_Files
{
    //Листинг папок
    static function listing ($url,$mode) {
        //Проверяем, является ли директорией
        if (is_dir($url)) {
            //Проверяем, была ли открыта директория
            if ($dir = opendir($url)) {
                //Сканируем директорию
                while ($file = readdir($dir)) {
                    //Убираем лишние элементы
                    if ($file != "." && $file != "..") {

                        //Если папка, то записываем в массив $folders
                        if(is_dir($url."/".$file)) {
                            $folders[] = $file;
                        }
                        //Если файл, то пишем в массив $file
                        else {$files[] = $file;}
                    }
                }
            }
            //Закрываем директорию
            closedir($dir);
        }
        //Если режим =1 то возвращаем массив с папками
        if($mode == 1) {return $folders;}
        //Если режим =0 то возвращаем массив с файлами
        if($mode == 0) {return $files;}
    }

    //Функция создания папки
    static function makedir ($url){
        //Вырезаем пробелы и хтмл-тэги
        $url = trim(htmlspecialchars($url));
        //Если папка создается возвращаем TRUE
        if(@mkdir($url)){return TRUE;}
        else{return FALSE;}
    }

    //Функция переименования
    static function frename ($url,$oldname,$nname){
        $nname = trim(htmlspecialchars($nname));
        $oldname = trim(htmlspecialchars($oldname));
        $url = trim(htmlspecialchars($url));
        if(@rename($url."/".$oldname,$url."/".$nname))

        {return TRUE; }
        else {return FALSE; }
    }

    static function removefile ($path) {
        if(unlink($path)) { return TRUE; }
        else {    return FALSE; }
    }

    static function updir( $path ){
        $last = strrchr( $path, "/" );
        $n1 = strlen( $last );
        $n2 = strlen( $path );
        return substr( $path, 0, $n2-$n1 );
    }

    //Получаем размер файла
    static function fsize($path) {
        return substr(filesize($path)/1024, 0, 4);
    }
}
