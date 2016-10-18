<?php namespace Sukohi\EloquentArray;

use \Sukohi\EloquentArray;

trait EloquentArrayTrait {

    private $eloquent_array_init_flag = false;
    private $eloquent_array_data = [];

    // Relationships

    public function array_values() {

        return $this->hasMany('Sukohi\EloquentArray\EloquentArrayItem', 'parent_id', 'id')
            ->where('model', __CLASS__);

    }

    public function getArray($name, $with_key = true) {

        $this->loadEloquentArray();
        $array = array_get($this->eloquent_array_data, $name, []);

        if($with_key) {

            return $array;

        }

        return array_values($array);

    }

    public function getArrayValue($name, $key, $default = '') {

        $array = $this->getArray($name);
        return array_get($array, $key, $default);

    }

    public function getAllArray() {

        $this->loadEloquentArray();
        return $this->eloquent_array_data;

    }

    public function setArray($name, $values) {

        $this->loadEloquentArray();
        $this->eloquent_array_data[$name] = $values;

    }

    public function setAllArray($data) {

        $this->loadEloquentArray();
        $this->eloquent_array_data = $data;

    }

    public function unsetArray($name, $key = '') {

        $this->loadEloquentArray();

        if(empty($key)) {

            unset($this->eloquent_array_data[$name]);

        } else {

            unset($this->eloquent_array_data[$name][$key]);

        }

    }

    public function setModelArray($model, $id) {

        $this->loadEloquentArray();
        $model_key = $this->getEloquentArrayModelKey($model);
        $this->eloquent_array_data[$model_key][$id] = $model;

    }

    public function setAllModelArray($data) {

        $this->loadEloquentArray();

        foreach ($data as $model => $values) {

            $this->checkModelExistence($model);
            $model_key = $this->getEloquentArrayModelKey($model);
            $this->eloquent_array_data[$model_key] = [];

            foreach ($values as $id) {

                $this->eloquent_array_data[$model_key][$id] = $model;

            }

        }

    }

    public function unsetModelArray($data) {

        $this->loadEloquentArray();

        foreach ($data as $model => $values) {

            $this->checkModelExistence($model);
            $model_key = $this->getEloquentArrayModelKey($model);

            foreach ($values as $id) {

                if(isset($this->eloquent_array_data[$model_key][$id])) {

                    unset($this->eloquent_array_data[$model_key][$id]);

                }

            }

        }

    }

    public function clearModelArray($models) {

        $this->loadEloquentArray();

        if(!is_array($models)) {

            $models = [$models];

        }

        foreach ($models as $model) {

            $this->checkModelExistence($model);
            $model_key = $this->getEloquentArrayModelKey($model);

            if(isset($this->eloquent_array_data[$model_key])) {

                unset($this->eloquent_array_data[$model_key]);

            }

        }

    }

    public function getModelArray($model) {

        $this->loadEloquentArray();
        $model_key = $this->getEloquentArrayModelKey($model);
        $models = array_get($this->eloquent_array_data, $model_key, []);
        $ids = [];

        foreach ($models as $id => $model) {

            $ids[] = $id;

        }

        $query = $model::select();

        if(count($models) == 0) {

            $query->where('id', -1);

        } else {

            $query->whereIn('id', $ids);

        }

        if(count($ids) > 0) {

            $query->orderByRaw(\DB::raw('FIELD(id, '. implode(',', $ids) .')'));

        }

        return $query->get();

    }

	public function saveArray() {

        $this->loadEloquentArray();

	    if(empty($this->id)) {

	        return false;

        }

	    $data = $this->eloquent_array_data;
		$this->clearArray();

		foreach($data as $name => $values) {

			foreach ($values as $key => $value) {

				$array_item = new EloquentArrayItem([
					'model' => __CLASS__,
					'parent_id' => $this->id,
					'name' => $name,
					'key' => $key,
					'value' => $value
				]);
				$array_item->save();

			}

		}

        $this->eloquent_array_data = $data;
        return true;

	}

	public function deleteArray($name) {

	    $this->unsetArray($name);
        return EloquentArrayItem::where('parent_id', $this->id)
            ->where('name', $name)
            ->where('model', __CLASS__)
            ->delete();

    }

	public function clearArray() {

        $this->loadEloquentArray();
        $this->eloquent_array_data = [];

		return EloquentArrayItem::where('parent_id', $this->id)
			->where('model', __CLASS__)
			->delete();

	}

	public function scopeWhereArray($query, $name, $operator = null, $value = null, $boolean = 'and') {

		$ids = EloquentArrayItem::distinct()
			->where('name', $name)
			->where('model', __CLASS__)
			->where('value', $operator, $value)
			->lists('parent_id');

        if($ids->count() == 0) {

            return $query;

        }

		return $query->whereIn('id', $ids, $boolean);

	}

	public function scopeOrWhereArray($query, $name, $operator = null, $value = null) {

		return $this->scopeWhereArray($query, $name, $operator, $value, 'or');

	}

	public function scopeOrderByArray($query, $name, $key, $direction = 'asc') {

        $ids = EloquentArrayItem::where('name', $name)
            ->where('model', __CLASS__)
            ->where('key', $key)
            ->orderBy('value', $direction)
            ->lists('parent_id');

        if($ids->count() == 0) {

            return $query;

        }

        $other_ids = self::whereNotIn('id', $ids)->lists('id');
        $ids = $ids->merge($other_ids);
        return $query->orderBy(\DB::raw('FIELD(id, '. $ids->implode(',') .')'));

    }

	private function loadEloquentArray() {

	    if(!$this->eloquent_array_init_flag) {

            $this->eloquent_array_init_flag = true;
            $this->load('array_values');
            $this->eloquent_array_data = [];

            if($this->array_values->count() > 0) {

                foreach ($this->array_values as $item) {

                    $name = $item->name;
                    $key = $item->key;
                    $this->eloquent_array_data[$name][$key] = $item->value;

                }

            }

        }

    }

    private function getEloquentArrayModelKey($model) {

        return 'models_'. md5($model);

    }

    private function checkModelExistence($model) {

        if(!class_exists($model)) {

            throw new \Exception('"'. $model .'" does not exist.');

        }

    }

}