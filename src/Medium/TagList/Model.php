<?php
/**
 * Ideal CMS (https://idealcms.ru/)
 *
 * @link      https://github.com/idealcms/idealcms репозиторий исходного кода
 * @copyright Copyright (c) 2012-2020 Ideal CMS (https://idealcms.ru)
 * @license   https://idealcms.ru/license.html LGPL v3
 */

namespace Ideal\Medium\TagList;

use Ideal\Core\Config;
use Ideal\Core\Db;
use Ideal\Medium\AbstractModel;

/**
 * Получение и сохранение связей между элементами структур и тегами
 */
class Model extends AbstractModel
{

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $db = Db::getInstance();
        $config = Config::getInstance();
        $table = $config->db['prefix'] . 'ideal_structure_tag';
        $sql = 'SELECT id, name FROM ' . $table . ' ORDER BY name ASC';
        $arr = $db->select($sql);

        $list = [];
        foreach ($arr as $item) {
            $list[$item['id']] = $item['name'];
        }

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlAdd($newValue = [])
    {
        $config = Config::getInstance();
        // Определяем структуру объекта, которому присваиваются теги
        $structure = $config->getStructureByClass(get_class($this->obj));

        $_sql = "DELETE FROM {$this->table} WHERE part_id='{{ objectId }}' AND structure_id='{$structure['id']}';";
        if (is_array($newValue) && (count($newValue) > 0)) {
            foreach ($newValue as $v) {
                $_sql .= "INSERT INTO {$this->table}
                              SET part_id='{{ objectId }}', tag_id='{$v}', structure_id='{$structure['id']}';";
            }
        }
        return $_sql;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        $fieldNames = array_keys($this->fields);
        $ownerField = $fieldNames[0];
        $elementsField = $fieldNames[1];

        $config = Config::getInstance();
        // Определяем структуру объекта, которому присваиваются теги
        $structure = $config->getStructureByClass(get_class($this->obj));

        $db = Db::getInstance();
        $owner = $this->obj->getPageData();

        if (!isset($owner['id'])) {
            // Если владелец списка ещё не создан, то и выбранных элементов в нём нет
            return [];
        }

        $_sql = "SELECT {$elementsField} FROM {$this->table}
                  WHERE {$ownerField}='{$owner['id']}' AND structure_id='{$structure['id']}'";
        $arr = $db->select($_sql);

        $list = [];
        foreach ($arr as $v) {
            $list[] = $v[$elementsField];
        }

        return $list;
    }
}
