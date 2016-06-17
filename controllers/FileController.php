<?php

namespace vommuan\filemanager\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use vommuan\filemanager\Module;
use vommuan\filemanager\models\MediaFile;
use vommuan\filemanager\assets\FilemanagerAsset;
use yii\helpers\Url;

class FileController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'update' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (defined('YII_DEBUG') && YII_DEBUG) {
            Yii::$app->assetManager->forceCopy = true;
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionFilemanager()
    {
        $this->layout = '@vendor/vommuan/yii2-filemanager/views/layouts/main';
        
        $model = new MediaFile();
        
        return $this->render('filemanager', [
            'dataProvider' => $model->search(),
        ]);
    }

    public function actionUploadmanager()
    {
        $this->layout = '@vendor/vommuan/yii2-filemanager/views/layouts/main';
        
        return $this->render('uploadmanager', [
            'model' => new MediaFile(),
        ]);
    }

    /**
     * Provides upload file
     * @return mixed
     */
    public function actionUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new MediaFile([
            'rename' => $this->module->rename,
        ]);
        
        $model->saveUploadedFile();
        $bundle = FilemanagerAsset::register($this->view);

        $response['files'][] = [
            'url'           => $model->url,
            'thumbnailUrl'  => $model->thumbFiles->getDefaultUrl($bundle->baseUrl),
            'name'          => $model->filename,
            'type'          => $model->type,
            'size'          => $model->file->size,
            'deleteUrl'     => Url::to(['file/delete', 'id' => $model->id]),
            'deleteType'    => 'POST',
        ];

        return $response;
    }

    /**
     * Updated mediafile by id
     * @param $id
     * @return array
     */
    public function actionUpdate($id)
    {
        $model = MediaFile::findOne($id);
        $message = Module::t('main', 'Changes not saved.');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $message = Module::t('main', 'Changes saved!');
        }

        Yii::$app->session->setFlash('mediafileUpdateResult', $message);

        return $this->renderPartial('info', [
            'model' => $model,
            'strictThumb' => null,
        ]);
    }

    /**
     * Delete model with files
     * @param $id
     * @return array
     */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = MediaFile::findOne($id);

        if ($model->isImage()) {
            $model->thumbFiles->delete();
        }

        $model->deleteFile();
        $model->delete();

        return ['success' => 'true'];
    }

    /**
     * Resize all thumbnails
     */
    public function actionResize()
    {
        $models = MediaFile::findByTypes(MediaFile::$imageFileTypes);

        foreach ($models as $model) {
            if ($model->isImage()) {
                $model->thumbFiles->delete();
                $model->thumbFiles->create();
            }
        }

        Yii::$app->session->setFlash('successResize');
        $this->redirect(Url::to(['default/settings']));
    }

    /** Render model info
     * @param int $id
     * @param string $strictThumb only this thumb will be selected
     * @return string
     */
    public function actionInfo($id, $strictThumb = null)
    {
        $model = MediaFile::findOne($id);
        return $this->renderPartial('info', [
            'model' => $model,
            'strictThumb' => $strictThumb,
        ]);
    }
}
