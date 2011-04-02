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
 * Ядро системы, выполняет базовую настройку, и запускает обработку запроса
 *
 */
class ABS_Kernel
{
    /*
     * Загрузка используемых файлов
     */
    private static function load()
    {
        require_once 'Zend/Loader.php';
        //Подключаем автозагрузку
        Zend_Loader::registerAutoload();

        require_once 'Zend/Session.php';
        require_once 'Zend/Config/Ini.php';
        require_once 'Zend/Registry.php';
        require_once 'Zend/Log.php';
        require_once 'Zend/Log/Writer/Stream.php';
        require_once 'Zend/Log/Writer/Db.php';
        require_once 'Zend/Translate.php';
        require_once 'Zend/Cache.php';
        require_once 'Zend/Db.php';
        require_once 'Zend/Db/Table.php';
        require_once 'Zend/Controller/Front.php';
        require_once 'Zend/Layout.php';
        require_once 'Zend/Locale.php';
        require_once 'Zend/Controller/Action.php';
        require_once 'Zend/Auth.php';
        require_once 'Zend/Acl.php';
        require_once 'Zend/Debug.php';
        require_once 'Zend/Paginator.php';
        require_once 'Abs/Functions.php';
        require_once 'Abs/Error.php';
        require_once 'Abs/Model/System.php';
    }

    /**
     * Подготовка основного меню (загрузка из файла)
     *
     */
    private function prepareMenu()
    {
        $config=Zend_Registry::get('config');
        if ($config->cache->enabled) {
            /** Zend_Cache */
            $cache=Zend_Registry::get('Zend_Cache');
        }

        $system=Abs_Model_System::getInstance();
        if(!$system->loadMenu()) {
            if (!file_exists(ROOT_DIR.'/menu.ini')) throw new Zend_Exception('Не найден файл конфигурации меню');
            $config_menu = new Zend_Config_Ini(ROOT_DIR.'/menu.ini', 'main');
            foreach ($config_menu as $item)
            {
                $system->addMenuItem($item->name, $item->controller.'/'.$item->action);
            }
        }
    }

    /**
     * Инициализация системы
     *
     */
    private static function init()
    {
        self::load();

        define(BASE_URL, baseUrl());

        // Получение конфигурации
        if (!file_exists(ROOT_DIR.'/config.ini')) throw new Zend_Exception('Не найден файл конфигурации');
        $config = new Zend_Config_Ini(ROOT_DIR.'/config.ini', 'development');
        Zend_Registry::set('config', $config);
        //var_dump(Zend_Registry::get('config'));
        Zend_Registry::set('isDebug', $config->debug->enabled);

        if (!self::isDebug() and extension_loaded('xdebug')) {
            xdebug_disable();
            //die('xd');
        }

        // Подключение логов
        if ($config->logger->enabled) {
            try {
                $logger = new Zend_Log();
                $writer_file = new Zend_Log_Writer_Stream(ROOT_DIR.'/'.$config->logger->file);
                $filter_crit = new Zend_Log_Filter_Priority(Zend_Log::CRIT);
                //$writer_file->addFilter($filter_crit);
                $logger->addWriter($writer_file);
                Zend_Registry::set('Zend_Log', $logger);
            } catch (Exception $e) {
                if (!headers_sent()) Header('Content-Type: text/html; charset=utf-8');
                die ("Проблема инициализации начальной конфигурации.\n В настоящее время работа невозможна.\n
                Пожалуйста повторите свой запрос позже");
            }
        }

        //Подключение кэша
        if ($config->cache->enabled) {
            $cache = Zend_Cache::factory($config->cache->frontend->name, $config->cache->backend->name,
            $config->cache->frontend->options->toArray(), $config->cache->backend->options->toArray());
            Zend_Registry::set('Zend_Cache', $cache);
        }

        $memoryManager=Zend_Memory::factory('File', $config->memory->toArray());
        Zend_Registry::set('Zend_Memory', $memoryManager);

        //Подключение локали
        Zend_Locale::setDefault('ru_RU');
        Zend_Translate::setCache($cache);
        $translate = new Zend_Translate('Array', ROOT_DIR.'/tools/languages', null, array('scan' => Zend_Translate::LOCALE_FILENAME));
        $translate->setLocale("ru");
        Zend_Registry::set('Zend_Translate', $translate);

        //Подключение Б.Д.
        try {
            $db = Zend_Db::factory($config->db->adapter, $config->db->config->toArray());
            Zend_Db_Table::setDefaultAdapter($db);
            $db->getProfiler()->setEnabled($config->db->profiler->enabled);
            Zend_Registry::set('Zend_Db',$db);
            $db->query('set names utf8');
        } catch (Exception $e) {
            if (!headers_sent()) Header('Content-Type: text/html; charset=utf-8');
            if (ABS_Kernel::isDebug()) {
                echo ($e);
            }
            die ("Проблема подключения к базе данных.\n В настоящее время работа невозможна.\n
                Пожалуйста повторите свой запрос позже");
        }

        //Подключаем логи к б.д.
        if ($config->logger->db->enabled) {
            $writer_db = new Zend_Log_Writer_Db($db, $config->logger->db->table,
            $config->logger->db->mapping->toArray());
            $logger->addWriter($writer_db);
        }

        //Подключаем кэш к таблицам б.д.
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);

        //Запускаем сессию
        if (!headers_sent()) {
            Zend_Session::start();
        }
        else {
            die('Не удается запустить сессию');
        }

        //Настройка почты
        if ($config->mail->smtp->enabled) {
            $transport = new Zend_Mail_Transport_Smtp($config->mail->smtp->host);
            Zend_Mail::setDefaultTransport($transport);
        }

        // Подключение файла с правилами маршрутизации
        require_once 'routers.php';

        //Подключаем меню
        ABS_Kernel::prepareMenu();
    }

    /**
     * Инициализация вида
     *
     * @param Zend_View_Abstract $view
     */
    private static function lib_init_view(Zend_View_Abstract $view)
    {
        $config=Zend_Registry::get('config');
         
        //особый случай  - требуются абсолютные пути
        $view->addHelperPath(ROOT_DIR.'/views/helpers');
        $view->addHelperPath($config->env->abs->root_dir.'/Views/helpers');
        $view->addBasePath($config->env->abs->root_dir.'/Views');

        $view->setEncoding('UTF-8');

        $view->doctype()->setDoctype('XHTML1_STRICT');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=UTF-8');
        $view->headMeta()->appendHttpEquiv('Content-Language', 'ru-RU');

        $view->logo='/tools/html/images/logo.png';
        $view->siteName= $config->site_name;

        $view->headTitle()->setSeparator(' / ');
        $view->headTitle()->append($view->siteName);

        $view->headLink()->appendStylesheet(BASE_URL.'/tools/html/css/basic.css');
        $view->headLink()->appendStylesheet(BASE_URL.'/tools/html/css/style.css');

        $view->headLink()->appendStylesheet(BASE_URL.'/tools/html/css/tablesorter.css');

        $view->headLink(array('rel' => 'favicon', 'href' => BASE_URL.'/favicon.ico'), 'PREPEND');
        $view->headLink(array('rel' => 'shortcut icon', 'href' => BASE_URL.'/favicon.ico'), 'PREPEND');
        $view->headLink(array('rel' => 'icon', 'href' => BASE_URL.'/favicon.ico'), 'PREPEND');

        //$view->headScript()->setAllowArbitraryAttributes(true);
        $view->headScript()->appendFile(BASE_URL.'/tools/html/js/jquery-1.2.3.js');
        $view->headScript()->appendFile(BASE_URL.'/tools/html/js/plugin/tablesorter/jquery-latest.js');
        $view->headScript()->appendFile(BASE_URL.'/tools/html/js/plugin/tablesorter/jquery.tablesorter.js');

        //Получение информации о текущем пользовател
        $view->currentUser = Zend_Auth::getInstance()->getIdentity();

        $view->loginLink=BASE_URL.'/auth/login';
        $view->logoutLink=BASE_URL.'/auth/logout';

        //справочники
        $view->thesaurus=Abs_Model_Db_Thesaurus::getInstance();
    }

    /**
     * Проверка, находится-ли система в режиме отладки
     *
     * @return bool
     */
    public static function isDebug()
    {
        if (Zend_Registry::isRegistered('config')) {
            $debug = (boolean) Zend_Registry::get('config')->debug->enabled;
        }
        else {
            $debug = false;
        }
        return $debug;
    }

    /**
     * Инициализация и предварительная настройка ACL
     *
     */
    private static function initAcl()
    {
        $acl = new Zend_Acl();
        Zend_Registry::set('Zend_Acl', $acl);
    }


    /*
     * Запуск системы (инициализация и обработка запроса)
     */
    public static function run()
    {
        self::init();
        $config=Zend_Registry::get('config');

        // Initialise Zend_Layout's MVC helpers
        Zend_Layout::startMvc(array('layoutPath' => $config->env->layout_dir));

        // setup controller
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->addControllerDirectory(ROOT_DIR.'/controllers');
         
        //
        $frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        //$frontController->throwExceptions(true);

        $frontController->registerPlugin(new Zend_Controller_Plugin_ActionStack());

        $frontController->registerPlugin(new Abs_Controller_Plugin_ControllerMenu());

        $frontController->registerPlugin(new Abs_Controller_Plugin_LoopShutdown());

        //setup acl
        self::initAcl();
        require_once 'Abs/Controller/Plugin/Acl.php';
        $frontController->registerPlugin(new Abs_Controller_Plugin_Acl());


        //prepare view
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        require_once ('Zend/View.php');
        $view = new Zend_View();
        self::lib_init_view($view);
        $viewRenderer->setView($view);

        //run
        $frontController->dispatch();
    }
}