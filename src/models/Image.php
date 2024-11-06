<?php

namespace siripravi\gallery\models;

use Yii;
use yii\helpers\Inflector;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use app\models\User;
/**
 * This is the model class for table "image".
 *
 * @property int $id
 * @property int $fk_id
 * @property string $fk_class
 * @property string $reference
 * @property string $slug
 * @property string $path
 * @property string $name
 * @property string $extension
 * @property string $filename
 * @property int $byteSize
 * @property string $mimeType
 * @property string $created_by
 * @property string $updated_at
 * @property string $updated_at
 */
class Image extends \yii\db\ActiveRecord
{
    public $imageSrc;

    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return \Yii::$app->gallery->imgTable;  //'{{%image}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => TimestampBehavior::class,
        ];
        $behaviors[] = [
            'class'     => SluggableBehavior::class,
            'value' => [$this, 'getSlug'] //https://github.com/yiisoft/yii2/issues/7773
        ];
        return $behaviors;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $fkName = \Yii::$app->gallery->fkName;
        return [
            [['extension', 'filename', 'byteSize', 'mimeType'], 'required'],
            [['byteSize', 'created_by','fk_id'], 'integer'],
            [['created_at', 'updated_at', $fkName, 'imageSrc', 'path','reference'], 'safe'],
            [['extension', 'filename', 'mimeType', 'path'], 'string', 'max' => 255],
            [['fk_class'],'string','max'=> 50]

          //  [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Yii::$app->gallery->userClass::class, 'targetAttribute' => ['created_by' => Yii::$app->user->userPk]],
        ];
    }

    /**
     * Renders this image.
     * @param string $version the image version to render.
     * @param string $alt the alternative text.
     * @param array $htmlOptions the html options.
     */
    public function render($version, $alt = '', $htmlOptions = array())
    {
        $src = \Yii::$app->gallery->getURL($this->id, $version);
        $htmlOptions['alt'] = $alt;
        echo Html::img($src, $htmlOptions);
    }

    /**
     * @return string the path for this image.
     */
    public function getPath()
    {
        return !empty($this->path) ? $this->path . '/' : '';
    }

    /**
     * @return string the path for this image.
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param $event
     * @return string
     * //https://github.com/yiisoft/yii2/issues/7773
     */
    public function getSlug($event)
    {
        if (!empty($event->sender->slug)) {
            return $event->sender->slug;
        }
        return Inflector::slug($event->sender->name);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fk_id' => 'Fk',
            'slug'  => 'slug',
            'path' => 'path',
            'extension' => 'Extension',
            'filename' => 'Filename',
            'byteSize' => 'Byte Size',
            'mimeType' => 'Mime Type',
            'created_at' => 'create time',
            'updated_at' => 'update time',
            'created_by' => 'create user'

        ];
    }
    /**
     * @inheritdoc
     */
   /* public function afterDelete()
    {
        /*$path = Yii::$app->params['uploads_path'] . '/' . $this->dir . '/';

        $filename = $path . $this->hash . '.' . $this->extension;

        if (file_exists($filename)) {
            unlink($filename);
        }*/
       // \Yii::$app->gallery->delete($this->id);
      //  parent::afterDelete();
    //}
   
}
