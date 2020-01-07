<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Field\RegexpList;

use Ideal\Field\AbstractController;

/**
 * Отображение редактирования поля в админке в виде textarea
 *
 * Пример объявления в конфигурационном файле структуры:
 *     'exceptions' => [
 *         'label' => 'Исключения',
 *         'sql'   => 'text',
 *         'type'  => 'Ideal_RegexpList'
 *     ],
 */
class Controller extends AbstractController
{

    /** {@inheritdoc} */
    protected static $instance;

    /**
     * {@inheritdoc}
     */
    public function getInputText()
    {
        return
            '<textarea class="form-control" name="' . $this->htmlName
            . '" id="' . $this->htmlName
            . '">' . htmlspecialchars($this->getValue()) . '</textarea>';
    }

    /**
     * {@inheritdoc}
     */
    public function parseInputValue($isCreate)
    {
        $item = parent::parseInputValue($isCreate);

        // Экранируем переводы строки для обработки каждой строки
        $string = str_replace("\r", '', $this->newValue);
        $lines = array_filter(explode("\n", $string));

        foreach ($lines as $line) {
            // Проверка на соответствие формату регулярного выражения, если нет, то уведомляем об этом
            if (!preg_match("/^\/.*\/[imsxADSUXJu]{0,11}$/", $line)) {
                $item['message'] = "Строка $line не удовлятворяет формату регулярных выражений.";
            }
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function pickupNewValue()
    {
        // В исключениях не нужны пустые строки
        $string = str_replace("\r", '', parent::pickupNewValue());
        $value = implode("\n", array_filter(explode("\n", $string)));
        return $value;
    }
}
