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
                    'id', $fkName, 'path', 'extension', 'filename', 'byteSize', 'mimeType', 'created'
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
                [$fkName, 'path', 'extension', 'filename', 'mimeType', 'created'],
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
            'created' => $this->created,

        ]);

        return $dataProvider;
    }
}
