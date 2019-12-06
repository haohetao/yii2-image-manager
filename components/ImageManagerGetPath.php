<?php

namespace haohetao\imagemanager\components;

use Yii;
use yii\base\Component;
use haohetao\imagemanager\models\ImageManager;
use yii\base\InvalidConfigException;
use yii\db\Connection;

class ImageManagerGetPath extends Component {

	/**
	 * @var null|string $mediaPath Folder path in which the images are stored
	 */
	public $mediaPath = null;
    /**
     * url中的路径部分
     * @var string
     */
	public $urlPath = '/image-manager';

	/**
	 * @var string|array $cachePath cache path(s) where store the resized images.
     * In case of multiple environments (frontend, backend) add more paths
     */
	public $cachePath = ["assets/imagemanager"];

	/**
	 * @var boolean $useFilename use original filename in generated cache file
	 */
	public $useFilename = true;

	/**
	 * @var boolean $useFilename use original filename in generated cache file
	 */
	public $absoluteUrl = false;

    /**
     * @var string The DB component name that the image model uses
     * This defaults to the default Yii DB component: Yii::$app->db
     * If this component is not set, the model will default to DB
     */
	public $databaseComponent = 'db';

	/**
	 * Init set config
	 */
	public function init() {
		parent::init();
        if ($this->mediaPath !== null) {
            $this->mediaPath = Yii::getAlias($this->mediaPath);
        }
        // If cachePath is not an array? Create an array
        if(!is_array($this->cachePath)){
            $this->cachePath = [$this->cachePath];
        }
		// Initialize the compontent with the configuration loaded from config.php
		\Yii::$app->set('imageresize', [
			'class' => 'noam148\imageresize\ImageResize',
			'cachePath' => $this->cachePath,
			'useFilename' => $this->useFilename,
			'absoluteUrl' => $this->absoluteUrl,
		]);

        // Check if the user input is correct
        $this->_checkVariables();
	}

	/**
	 * Get the path for the given image_manager_id record
	 * @param int $ImageManager_id ImageManager record for which the path needs to be generated
	 * @param int $width Thumbnail image width
	 * @param int $height Thumbnail image height
	 * @param string $thumbnailMode Thumbnail mode
	 * @return null|string Full path is returned when image is found, null if no image could be found
	 */
	public function getImagePath($ImageManager_id, $width = 400, $height = 400, $thumbnailMode = "outbound") {
		//default return
		$return = null;
		$mImageManager = ImageManager::findOne($ImageManager_id);

		//check if not empty
		if ($mImageManager !== null) {

			$sMediaPath = null;
			if ($this->mediaPath !== null) {
				$sMediaPath = $this->mediaPath;
			}

			$sFileExtension = pathinfo($mImageManager->image_manager_filename, PATHINFO_EXTENSION);
			//get image file path
			$sImageFilePath = $sMediaPath . '/' . $mImageManager->image_manager_id . '_' . $mImageManager->image_manager_filehash . '.' . $sFileExtension;
			//check file exists
			if (file_exists($sImageFilePath)) {
				$return = \Yii::$app->imageresize->getUrl($sImageFilePath, $width, $height, $thumbnailMode, null, $mImageManager->image_manager_filename);
			} else {
				$return = null; //isset(\Yii::$app->controller->module->assetPublishedUrl) ? \Yii::$app->controller->module->assetPublishedUrl. "/img/img_no-image.png" : null;
			}
		}
		return $return;
	}

    /**
     * 返回原始图片路径或URL
     * @param $id
     * @param bool $absoluteUrl
     * @return string|null
     */
	public function getOriginalPath($id, $absoluteUrl = true)
    {
        $return = null;
        $url = '';
        if ($absoluteUrl) {
            if (!isset(Yii::$app->params['imageCommonUrl'])) {
                throw new InvalidConfigException('imageCommonUrl param must exists in params.php o params-local.php.');
            }
            $url = Yii::$app->params['imageCommonUrl'];
        }
        $model = ImageManager::findOne($id);
        $return = $url . $this->urlPath . '/' . $id . '_' . $model->image_manager_filehash . '.' . pathinfo($model->image_manager_filename, PATHINFO_EXTENSION);
        return $return;
    }

    /**
     * Check if the user configurable variables match the criteria
     * @throws InvalidConfigException
     */
	private function _checkVariables() {
	    // Check to make sure that the $databaseComponent is a string
        if (! is_string($this->databaseComponent)) {
            throw new InvalidConfigException("Image Manager Component - Init: Database component '$this->databaseComponent' is not a string");
        }

        // Check to make sure that the $databaseComponent object exists
        if (Yii::$app->get($this->databaseComponent, false) === null) {
            throw new InvalidConfigException("Image Manager Component - Init: Database component '$this->databaseComponent' does not exists in application configuration");
        }

        // Check to make sure that the $databaseComponent is a yii\db\Connection object
        if ($this->databaseComponent instanceof Connection) {
            throw new InvalidConfigException("Image Manager Component - Init: Database component '$this->databaseComponent' is not of type 'Connection' ");
        }
    }

}
