<?php

namespace app\models;

use Yii;
use yii\base\Model;

class JsonDataModel extends Model
{
    protected static $dir = 'test/test';

    protected $id;

    protected function beforeSave()
    {
        return true;
    }

    protected function afterSave()
    {
    }

    public function save()
    {
        $this->validate();
        //  dump($this->phone, 1);
        if (!$this->getErrors() && $this->beforeSave()) {
            $this->_save();
            $this->afterSave();

            return true;
        }

        return false;
    }

    private function _save()
    {
        if (!$this->id) {
            $this->setId();
        }

        file_put_contents(static::getDir().'/'.$this->id, json_encode($this->toArray()));
    }

    protected function beforeDelete()
    {
        return true;
    }

    protected function afterDelete()
    {
    }

    public function delete($toFile = true)
    {
        if ($this->beforeDelete()) {
            $this->_delete();
            $this->afterDelete();

            return true;
        }

        return false;
    }

    public function _delete()
    {
        if (is_file(static::getDir().'/'.$this->id)) {
            unlink(static::getDir().'/'.$this->id);
        }
    }

    public function setId($id = null)
    {
        if (!$id) {
            $id = uniqid(time());
        }
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    protected static function getDir()
    {
        return Yii::getAlias('@app').'/'.static::$dir;
    }

    protected static function jsonData($id)
    {
        return is_file(static::getDir().'/'.$id) ? json_decode(file_get_contents(static::getDir().'/'.$id), true) : [];
    }

    public function afterFind()
    {
    }

    public static function findById($id)
    {
        $jsonData = static::jsonData($id);
        if ($jsonData) {
            $obj = new static($jsonData);
            $obj->setId($id);
            $obj->afterFind();

            return $obj;
        }

        return  null;
    }

    public static function findData()
    {
        $result = [];
        $scanDir = scandir(static::getDir());
        if ($scanDir) {
            foreach ($scanDir as $v) {
                $obj = static::findById($v);
                if ($obj) {
                    $result[$v] = $obj;
                }
            }
        }

        return $result;
    }
}
