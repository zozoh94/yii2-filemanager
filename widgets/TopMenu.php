<?php
namespace zozoh94\filemanager\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use zozoh94\filemanager\Module;

class TopMenu extends Widget
{
	public $controller = 'file';
	
	/**
	 * @return array menu items
	 */
	protected function getManageFilesItems()
	{
		return [
			[
				'label' => Html::tag('span', '', ['class' => 'glyphicon glyphicon-picture']) . ' ' . Module::t('main', 'Files'),
				'url' => Url::to([$this->controller . '/index']),
				'encode' => false,
			],
		];
	}
	
	/**
	 * @return array menu items
	 */
	protected function getManageSettingsItems()
	{
		return [
			[
				'label' => Html::tag('span', '', ['class' => 'glyphicon glyphicon-wrench']) . ' ' . Module::t('main', 'Settings'),
				'url' => Url::to(['setting/index']),
				'encode' => false,
			],
		];
	}
	
	public function run()
	{
		$menuItems = [];
		
		if (!Module::getInstance()->rbac || Yii::$app->user->can('filemanagerManageOwnFiles')) {
			$menuItems = array_merge($menuItems, $this->getManageFilesItems());
		}
		
		if (!Module::getInstance()->rbac || Yii::$app->user->can('filemanagerManageSettings')) {
			$menuItems = array_merge($menuItems, $this->getManageSettingsItems());
		}
		
		return $this->render('top-menu', [
			'menuItems' => $menuItems,
		]);
	}
}