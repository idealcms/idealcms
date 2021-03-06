<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

return array(
    'params' => array(
        'has_table' => true
    ),
    'fields' => array(
        'part_id' => array(
            'label' => 'Идентификатор страницы',
            'sql'   => 'int(11)',
        ),
        'tag_id' => array(
            'label' => 'Идентификатор тега',
            'sql'   => 'int(11)',
        ),
        'structure_id' => array(
            'label' => 'Структура, элементу которой присвоен тег',
            'sql'   => 'char(15)',
        )
    )
);
