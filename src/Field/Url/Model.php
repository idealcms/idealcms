<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Field\Url;

use Ideal\Core\Config;
use Ideal\Core\PluginBroker;

/**
 * Модель для работы с основными задачами по URL
 *
 * * Установка родительского URL
 * * Определение URL элемента
 * * Транслитерация URL
 * * Транслитерация файловых имён
 *
 */
class Model
{
    // TODO сделать возможность определять url Не только по полю url

    /** @var string Родительский URL, используемый для построения URL элементов на этом уровне */
    protected $parentUrl;

    /**
     * Удаление символов, неприменимых в URL
     *
     * @param string $nm исходная ссылка
     * @return string преобразованная ссылка
     */
    public static function translitUrl($nm)
    {
        $nm = Model::translit($nm);
        $nm = mb_strtolower($nm);
        $arr = array(
            '@' => '',
            '$' => '',
            '^' => '',
            '+' => '',
            '|' => '',
            '{' => '',
            '}' => '',
            '>' => '',
            '<' => '',
            ':' => '',
            ';' => '',
            '[' => '',
            ']' => '',
            '\\' => '',
            '*' => '',
            '(' => '',
            ')' => '',
            '!' => '',
            '#' => 'N',
            '—' => '',
            '/' => '-',
            '«' => '',
            '»' => '',
            '.' => '',
            '№' => 'N',
            '"' => '',
            "'" => '',
            '?' => '',
            ' ' => '-',
            '&' => '',
            ',' => '',
            '%' => ''
        );
        $nm = strtr($nm, $arr);
        return $nm;
    }

    /**
     * Транслитерация русских букв в латинские
     *
     * @param string $nm - исходная строка
     * @return string преобразованная строка
     */
    public static function translit($nm)
    {
        $arr = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'j',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'shh',
            'ы' => 'y',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',
            'ь' => '',
            'ъ' => '',
            'А' => 'a',
            'Б' => 'b',
            'В' => 'v',
            'Г' => 'g',
            'Д' => 'd',
            'Е' => 'e',
            'Ё' => 'e',
            'Ж' => 'zh',
            'З' => 'z',
            'И' => 'i',
            'Й' => 'j',
            'К' => 'k',
            'Л' => 'l',
            'М' => 'm',
            'Н' => 'n',
            'О' => 'o',
            'П' => 'p',
            'Р' => 'r',
            'С' => 's',
            'Т' => 't',
            'У' => 'u',
            'Ф' => 'f',
            'Х' => 'h',
            'Ц' => 'c',
            'Ч' => 'ch',
            'Ш' => 'sh',
            'Щ' => 'shh',
            'Ы' => 'y',
            'Э' => 'e',
            'Ю' => 'yu',
            'Я' => 'ya',
            'Ь' => '',
            'Ъ' => ''
        );
        $nm = strtr($nm, $arr);
        return $nm;
    }

    /**
     * Отрезает стандартный суффикс от ссылки
     *
     * @param $link
     * @return string
     */
    public function cutSuffix($link)
    {
        $config = Config::getInstance();
        $link = substr($link, 0, -strlen($config->urlSuffix));
        return $link;
    }

    /**
     * Получение url для элемента $lastPart на основании ранее установленного пути или префикса $parentUrl
     *
     * @param array $lastPart Массив с основными данными об элементе
     * @return string Сгенерированный URL этого элемента
     */
    public function getUrl($lastPart)
    {
        return $this->getUrlWithPrefix($lastPart, $this->parentUrl);
    }

    /**
     * Получение url для элемента $lastPart на основании ранее установленного пути или префикса $parentUrl
     *
     * Метод генерирует событие onGetUrl, которое могут перехватывать плагины ддя создания специальных правил
     * получения URL.
     *
     * @param array  $lastPart Массив с основными данными об элементе
     * @param string $parentUrl
     * @return string Сгенерированный URL этого элемента
     */
    public static function getUrlWithPrefix($lastPart, $parentUrl = '')
    {
        $lastUrlPart = $lastPart['url'];

        if ($parentUrl == '---') {
            // В случае, когда родительский url неопределён
            return '---';
        }

        $config = Config::getInstance();
        if ($lastUrlPart == '/' || $lastUrlPart == '') {
            $lastUrlPart = '/';
            // Ссылка на главную обрабатывается особым образом
            if ($config->cms['startUrl'] != '') {
                $lastUrlPart = $config->cms['startUrl'] . '/';
            }
            return $lastUrlPart;
        }

        $pluginBroker = PluginBroker::getInstance();
        $arr = ['last' => $lastPart, 'parent' => $parentUrl];
        $arr = $pluginBroker->makeEvent('onGetUrl', $arr);
        $lastUrlPart = $arr['last']['url'];

        if (strpos($lastUrlPart, 'http:') === 0
            || strpos($lastUrlPart, 'https:') === 0
            || strpos($lastUrlPart, '/') === 0
            || empty($lastUrlPart)
        ) {
            // Если это уже сформированная или пустая ссылка, её и возвращаем
            return $lastUrlPart;
        }

        if ($parentUrl == '' || $parentUrl == '/') {
            $parentUrl = $config->cms['startUrl'];
        } elseif (is_array($parentUrl)) {
            $parentUrl = implode('/', $parentUrl);
        }

        $url = $parentUrl;

        // Если URL предка нельзя составить
        if ($url == '---') {
            return '---';
        }

        $url .= '/';

        // Добавляем дочерний url
        if ($url != $lastUrlPart) {
            // Сработает только для всех ссылок, кроме главной '/'
            $url .= $lastUrlPart . $config->urlSuffix;
        }

        return $url;
    }

    /**
     * Установка родительского URL ($this->parentUrl) на основании $path
     *
     * @param array $path Путь до элемента, для которого нужно определить URL
     * @return string Родительский URL, который можно использовать для построения URL
     */
    public function setParentUrl($path)
    {
        // Обратиться к модели для получения своей части url, затем обратиться
        // к более старшим структурам пока не доберёмся до конца

        if (count($path) > 2 && $path[1]['url'] == '/') {
            // Мы находимся внутри главной - в ней url не работают
            return '---';
        };

        // TODO если первая структура не стартовая, то нужно определить путь от стартовой структуры

        // Если первая структура в пути — стартовая структура, то просто объединяем url

        if (!isset($path[0]['url'])) {
            // Путь может быть не задан в случае установки parentUrl для главной странице
            $this->parentUrl = '';
            return '';
        }

        $prefix = $url = '';

        // Объединяем все участки пути
        foreach ($path as $v) {
            if (isset($v['is_skip']) && $v['is_skip']) {
                continue;
            }
            if (strpos($v['url'], 'http:') === 0
                || strpos($v['url'], 'https:') === 0
                || strpos($v['url'], '/') === 0
            ) {
                // Если в одном из элементов пути есть ссылки на другие страницы, то путь построить нельзя
                return '---';
            }
            $url .= $prefix . $v['url'];
            $prefix = '/';
        }

        $this->parentUrl = $url;

        return $url;
    }

    /**
     * Транслитерация файлов без изменения букв в расширении
     *
     * @param string $name Исходное название файла
     * @return string Преобразованное название файла
     */
    public function translitFileName($name)
    {
        $ext = '';
        $posDot = mb_strrpos($name, '.');
        if ($posDot != 0) {
            $name = mb_substr($name, 0, $posDot);
            $ext = '.' . mb_substr($name, $posDot + 1);
        }
        $name = Model::translit($name);
        $name = strtolower($name);
        $arr = array(
            '@' => '',
            '$' => '',
            '^' => '',
            '+' => '',
            '|' => '',
            '{' => '',
            '}' => '',
            '>' => '',
            '<' => '',
            ':' => '',
            ';' => '',
            '[' => '',
            ']' => '',
            '\\' => '',
            '*' => '',
            '(' => '',
            ')' => '',
            '!' => '',
            '#' => 'N',
            '—' => '',
            '/' => '-',
            '«' => '',
            '»' => '',
            '.' => '',
            '№' => 'N',
            '"' => '',
            '\'' => '',
            '?' => '',
            ' ' => '-',
            '&' => '',
            ',' => '',
            '%' => ''
        );
        $name = strtr($name, $arr);
        return $name . $ext;
    }

    /**
     * Построение url на основе cid элемета $part
     *
     * @param array $part Массив с данными элемента, для которого нужно построить url по cid
     * @param string $structureName Название структуры, в которой находится элемент
     * @return string
     */
    public function getUrlByCid($part, $structureName)
    {
        $structureClass = explode('_', $structureName);
        $structureClass = '\\' . $structureClass[0] . '\\Structure\\' . $structureClass[1] . '\\Site\\Model';

        /** @var \Ideal\Structure\Part\Site\Model $structureModel */
        $structureModel = new $structureClass($part['prev_structure']);

        $structureModel->setPageData($part);
        $path = $structureModel->getLocalPath();
        array_pop($path);

        $url = [];
        foreach ($path as $item) {
            $url[] = $item['url'];
        }

        return self::getUrlWithPrefix($part, $url);
    }

    /**
     * Установка пути для построения URL по prev_structure
     * Используется для построения пути у повторяющихся элементов на одном уровне, если путь для них не построен
     * Например, список товаров в корзине и т.п.
     *
     * @param string $prevStructure prev_structure родительского элемента
     * @return array Путь, включая и родительский элемент
     * @throws \Exception В случае, если родтельский элемент не найден (неправильная prev_structure)
     */
    public function setPathByPrevStructure($prevStructure)
    {
        $config = Config::getInstance();

        // Находим элемент по prevStructure
        list($structureId, $elementId) = explode('-', $prevStructure);
        $structure = $config->getStructureById($structureId);
        list($mod, $structure) = explode('_', $structure['structure']);
        $class = '\\' . $mod . '\\Structure\\' . $structure . '\\Site\\Model';

        /** @var \Ideal\Core\Site\Model $model */
        $model = new $class('');
        $model->initPageDataById($elementId);

        $path = $model->detectPath();
        $this->setParentUrl($path);

        return $path;
    }
}
