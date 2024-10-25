<?php

namespace siripravi\gallery\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Description of ImageSearch
 *
 * @author prov
 */
class ImageSearch extends Image
{
    public $all;
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    public function rules()
    {
        $fkName = \Yii::$app->gallery->fkName;
        return [
            [
                ['byteSize'],
                'integer',
            ],
            [
                [
                    'id', $fkName, 'path', 'extension', 'filename', 'byteSize', 'mimeType', 'created_at'
                ],
                'safe'
            ],
            [
                [
                    'extension', 'filename', 'byteSize', 'mimeType'
                ],
                'required',
            ],

            [
                [$fkName, 'path', 'extension', 'filename', 'mimeType', 'created_at'],
                'string',
                'max' => 255,
            ],
        ];
    }
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $fkName = \Yii::$app->gallery->fkName;
        $query = Image::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            // '' => $this->{$fkName},
            'path' => $this->path,
            'extension' => $this->extension,
            'filename' => $this->filename,
            'byteSize' => $this->byteSize,
            'mimeType' => $this->mimeType,
            'created_at' => $this->created_at,

        ]);

        return $dataProvider;
    }
}
