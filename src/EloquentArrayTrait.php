<?php namespace Sukohi\EloquentArray;

use \Sukohi\EloquentArray;

trait EloquentArrayTrait {

    private $eloquent_array_init_flag = false;
    private $eloquent_array_data = [];

    // Relationships

    public function array_values() {

        return $this->hasMany('Sukohi\EloquentArray\EloquentArrayItem', 'parent_id', 'id');

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

	public function scopeOrderByArray($query, $name, $direction = 'asc') {

        $ids = EloquentArrayItem::where('name', $name)
            ->where('model', __CLASS__)
            ->orderBy('value', $direction)
            ->lists('id');

        if($ids->count() == 0) {

            return $query;

        }

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

}