<?php

namespace app\models;

use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 */
class Category extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id'], 'default', 'value' => null],
            [['parent_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'parent_id' => 'Parent ID',
            'level' => 'Level',
        ];
    }

    /**
     * get parent list for form
     * format $arr = ['id' => 'name', ...]
     * @return array
     */
    public static function getParentList()
    {
        return ArrayHelper::map(
            Category::find()
                ->select(['name', 'id'])
                ->indexBy('id')
                ->asArray()->all(),
            'id',
            'name'
        );
    }


    /**
     * @return int|mixed
     * @throws Exception
     */
    public function calcLevel($parentId, $currentLevel = 0)
    {
        if ($parentId === null) {
            return $currentLevel;
        }

        if ($currentLevel > \Yii::$app->params['max_level']) {
            throw new Exception('category level critical deep');
        }

        $stepParentId = Category::find()
            ->select('parent_id')
            ->where(['id' => $parentId])
            ->asArray()
            ->scalar();

        if ($stepParentId === false) {
            throw new Exception('the category chain is broken. it is necessary to recalculate');
        }

        return $this->calcLevel($stepParentId, $currentLevel + 1);
    }
}
