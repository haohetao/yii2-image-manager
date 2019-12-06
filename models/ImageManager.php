<?php

namespace noam148\imagemanager\models;

use noam148\imagemanager\Module;
use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "ImageManager".
 *
 * @property integer $image_manager_id
 * @property string $image_manager_filename
 * @property string $image_manager_filehash
 * @property string $image_manager_create_datetime
 * @property string $image_manager_update_datetime
 * @property string $image_manager_create_account
 * @property string $image_manager_update_account
 */
class ImageManager extends \yii\db\ActiveRecord {

	/**
	 * Set Created date to now
	 */
	public function behaviors() {
	    $aBehaviors = [];

	    // Add the time stamp behavior
        $aBehaviors[] = [
            'class' => TimestampBehavior::className(),
            'createdAtAttribute' => 'image_manager_create_datetime',
            'updatedAtAttribute' => 'image_manager_update_datetime',
            'value' => new Expression('NOW()'),
        ];

        // Get the imagemanager module from the application
        $moduleImageManager = Yii::$app->getModule('imagemanager');
        /* @var $moduleImageManager Module */
        if ($moduleImageManager !== null) {
            // Module has been loaded
            if ($moduleImageManager->setBlameableBehavior) {
                // Module has blame able behavior
                $aBehaviors[] = [
                    'class' => BlameableBehavior::class,
                    'createdByAttribute' => 'image_manager_create_account',
                    'updatedByAttribute' => 'image_manager_update_account',
                ];
            }
        }

		return $aBehaviors;
	}

	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%image_manager}}';
	}

    /**
     * Get the DB component that the model uses
     * This function will throw error if object could not be found
     * The DB connection defaults to DB
     * @return null|object
     */
	public static function getDb() {
        // Get the image manager object
        $oImageManager = Yii::$app->get('imagemanager', false);

        if($oImageManager === null) {
            // The image manager object has not been set
            // The normal DB object will be returned, error will be thrown if not found
            return Yii::$app->get('db');
        }

        // The image manager component has been loaded, the DB component that has been entered will be loaded
        // By default this is the Yii::$app->db connection, the user can specify any other connection if needed
        return Yii::$app->get($oImageManager->databaseComponent);
    }

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['image_manager_filename', 'image_manager_filehash'], 'required'],
			[['image_manager_create_datetime', 'modified'], 'safe'],
			[['image_manager_filename'], 'string', 'max' => 128],
			[['image_manager_filehash'], 'string', 'max' => 32],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'image_manager_id' => Yii::t('imagemanager', 'ID'),
			'image_manager_filename' => Yii::t('imagemanager', 'File Name'),
			'image_manager_filehash' => Yii::t('imagemanager', 'File Hash'),
			'image_manager_create_datetime' => Yii::t('imagemanager', 'Created'),
			'image_manager_update_datetime' => Yii::t('imagemanager', 'Modified'),
			'image_manager_create_account' => Yii::t('imagemanager', 'Created by'),
			'image_manager_update_account' => Yii::t('imagemanager', 'Modified by'),
		];
	}

	public function afterDelete()
    {
        parent::afterDelete();

        // Check if file exists
        if (file_exists($this->getImagePathPrivate())) {
            unlink($this->getImagePathPrivate());
        }
    }

    /**
	 * Get image path private
	 * @return string|null If image file exists the path to the image, if file does not exists null
	 */
	public function getImagePathPrivate() {
		//set default return
		$return = null;
		//set media path
		$sMediaPath = \Yii::$app->imagemanager->mediaPath;
		$sFileExtension = pathinfo($this->image_manager_filename, PATHINFO_EXTENSION);
		//get image file path
		$sImageFilePath = $sMediaPath . '/' . $this->image_manager_id . '_' . $this->image_manager_filehash . '.' . $sFileExtension;
		//check file exists
		if (file_exists($sImageFilePath)) {
			$return = $sImageFilePath;
		}
		return $return;
	}

	/**
	 * Get image data dimension/size
	 * @return array The image sizes
	 */
	public function getImageDetails() {
		//set default return
		$return = ['width' => 0, 'height' => 0, 'size' => 0];
		//set media path
		$sMediaPath = \Yii::$app->imagemanager->mediaPath;
		$sFileExtension = pathinfo($this->image_manager_filename, PATHINFO_EXTENSION);
		//get image file path
		$sImageFilePath = $sMediaPath . '/' . $this->image_manager_id . '_' . $this->image_manager_filehash . '.' . $sFileExtension;
		//check file exists
		if (file_exists($sImageFilePath)) {
			$aImageDimension = getimagesize($sImageFilePath);
			$return['width'] = isset($aImageDimension[0]) ? $aImageDimension[0] : 0;
			$return['height'] = isset($aImageDimension[1]) ? $aImageDimension[1] : 0;
			$return['size'] = Yii::$app->formatter->asShortSize(filesize($sImageFilePath), 2);
		}
		return $return;
	}

}
