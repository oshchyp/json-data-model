<?php

namespace app\modules\admin\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

class JsonDataModel extends Model
{
    //protected static $jsonPath = '';

    protected static $jsonData;

    protected static $keyDataInFile;

    protected $formName;

    protected $key;

    public function beforeSave()
    {
        return true;
    }

    public function afterSave()
    {
    }

    public function save($toFile = false)
    {
        if ($this->validate() && $this->beforeSave()) {
            if (isset(static::$jsonData[$this->key])) {
                static::$jsonData[$this->key] = $this->toArray();
            } else {
                static::$jsonData[] = $this->toArray();
            }
            if ($toFile) {
                static::saveToFile();
            }
            $this->afterSave();
        }
    }

    public function beforeDelete()
    {
        return true;
    }

    public function afterDelete()
    {
    }

    public function delete($toFile = true)
    {
        if ($this->beforeDelete() && $this->key && isset(static::$jsonData[$this->key])) {
            unset(static::$jsonData[$this->key]);
        }
        if ($toFile) {
            static::saveToFile();
        }
        $this->afterDelete();
    }

    public static function saveToFile()
    {
        static::assignIds();
        file_put_contents(static::$jsonPath, json_encode(static::$keyDataInFile ? [static::$keyDataInFile => static::$jsonData] : static::$jsonData));
    }

    public static function assignIds()
    {
        if (static::$jsonData) {
            static::$jsonData = array_values(static::$jsonData);
            foreach (static::$jsonData as $k => $v) {
                static::$jsonData[$k]['id'] = $k + 1;
            }
        }
    }

    public function formName()
    {
        if (!$this->formName) {
            return parent::formName();
        } else {
            return $this->formName;
        }
    }

    public function setFormNameMultiSave()
    {
        $this->formName = parent::formName().'MultiSave['.$this->id.']';
    }

    public static function getJsonData()
    {
        return static::$jsonData;
    }

    public static function setJsonData($jsonData = null)
    {
        if ($jsonData === null) {
            $info = json_decode(file_get_contents(static::$jsonPath), true);
            if ($key = static::$keyDataInFile) {
                static::$jsonData = isset($info[$key]) ? $info[$key] : [];
            } else {
                static::$jsonData = $info;
            }
        } else {
            static::$jsonData = $jsonData;
        }
    }

    public static function findObjById($data)
    {
        $objById = static::findById($data);

        return $objById ? $objById : new static();
    }

    public static function findById($data)
    {
        $id = is_array($data) ? (int) ArrayHelper::getValue($data, 'id') : (int) $data;
        if ($jsonData = static::$jsonData) {
            foreach ($jsonData as $k => $v) {
                if (isset($v['id']) && (int) $v['id'] == $id) {
                    $object = new static($v);
                    $object->key = $k;

                    return $object;
                }
            }
        }

        return null;
    }

    public static function findData()
    {
        $result = [];
        if ($jsonData = static::$jsonData) {
            foreach ($jsonData as $k => $v) {
                $result[$k] = new static($v);
                $result[$k]->key = $k;
                $result[$k]->setFormNameMultiSave();
            }
        }

        return $result;
    }
}
