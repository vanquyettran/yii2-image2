<?php

namespace common\modules\image\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ImageSearch represents the model behind the search form about `common\modules\image\models\Image`.
 */
class ImageSearch extends Image
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'creator_id', 'updater_id', 'active', 'status', 'create_time', 'update_time', 'width', 'height', 'quality'], 'integer'],
            [['file_basename', 'path', 'name', 'file_extension', 'mime_type', 'description', 'resize_labels', 'encode_data', 'aspect_ratio'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Image::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'creator_id' => $this->creator_id,
            'updater_id' => $this->updater_id,
            'active' => $this->active,
            'status' => $this->status,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time,
            'quality' => $this->quality,
            'width' => $this->width,
            'height' => $this->height,
        ]);

        $query->andFilterWhere(['like', 'file_basename', $this->file_basename])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'file_extension', $this->file_extension])
            ->andFilterWhere(['like', 'mime_type', $this->mime_type])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'resize_labels', $this->resize_labels])
            ->andFilterWhere(['like', 'encode_data', $this->encode_data])
            ->andFilterWhere(['like', 'aspect_ratio', $this->aspect_ratio]);

        return $dataProvider;
    }
}
