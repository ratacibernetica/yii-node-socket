<?php
namespace YiiNodeSocket\Components\Db\Mysql;

use YiiNodeSocket\Components\Db\BaseDriver;
use YiiNodeSocket\Models\AModel;

class MysqlDriver extends BaseDriver {

	/**
	 * @param array $config
	 *
	 * @return void
	 */
	public function init(array $config) {}


	/**
	 * @param AModel $model
	 *
	 * @return boolean
	 */
	public function save(AModel $model) {
		$modelClassName = $this->resolveModelName($model);
		if ($model->getIsNewRecord()) {
			$newModel = new $modelClassName();
		} else {
			$newModel = $this->_resolveModel($model)->findByPk($model->id);
			if (!isset($newModel)) {
				$newModel = new $modelClassName();
			}
		}
		/** @var \CActiveRecord $model */
		$newModel->setAttributes($model->getAttributes());
		if ($newModel->save()) {
			$model->setAttributes($newModel->getAttributes());
			return true;
		}
		$model->addErrors($newModel->getErrors());
		return false;
	}

	/**
	 * @param AModel $model
	 *
	 * @return boolean
	 */
	public function refresh(AModel $model) {
		if (!$model->getIsNewRecord()) {
			$m = $this->_resolveModel($model)->findByPk($model->id);
			if ($m) {
				$model->setAttributes($m->getAttributes());
				return true;
			}
		}
		return false;
	}

	/**
	 * @param AModel $model
	 *
	 * @return boolean
	 */
	public function delete(AModel $model) {
		if (!$model->getIsNewRecord()) {
			$m = $this->_resolveModel($model)->findByPk($model->id);
			if ($m) {
				return $m->delete();
			}
		}
		return true;
	}

	/**
	 * @param        $pk
	 * @param AModel $model
	 *
	 * @return AModel
	 */
	public function findByPk($pk, AModel $model) {
		$foundModel = $this->_resolveModel($model)->findByPk($pk);
		if ($foundModel) {
			$newInstance = $model->newInstance('update');
			$newInstance->setAttributes($foundModel->getAttributes());
			return $newInstance;
		}
		return null;
	}

	/**
	 * @param array  $attributes
	 * @param AModel $model
	 *
	 * @return AModel
	 */
	public function findByAttributes(array $attributes, AModel $model) {
		$arModel = $this->_resolveModel($model)->findByAttributes($attributes);
		if ($arModel) {
			$newModel = $model->newInstance('update');
			$newModel->setAttributes($arModel->getAttributes());
			return $newModel;
		}
		return null;
	}

	/**
	 * @param array  $attributes
	 * @param AModel $model
	 *
	 * @return AModel[]
	 */
	public function findAllByAttributes(array $attributes, AModel $model) {
		$models = $this->_resolveModel($model)->findAllByAttributes($attributes);
		$foundedModels = array();
		foreach ($models as $m) {
			$newInstance = $model->newInstance('update');
			$newInstance->setAttributes($m->getAttributes());
			$foundedModels[] = $newInstance;
		}
		return $foundedModels;
	}

	/**
	 * @param AModel $model
	 *
	 * @return string
	 */
	protected function resolveModelName(AModel $model) {
		return '\YiiNodeSocket\Components\Db\Mysql\Models\\' . 'Ns' . parent::resolveModelName($model);
	}

	/**
	 * @param AModel $nsModel
	 *
	 * @return \CActiveRecord
	 */
	private function _resolveModel(AModel $nsModel) {
		return call_user_func(array($this->resolveModelName($nsModel), 'model'));
	}
}