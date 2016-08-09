<?php namespace Sukohi\EloquentArray;

use \Sukohi\EloquentArray;

trait EloquentArrayTrait {

    private $eloquent_array_init_flag = false;
    private $eloquent_array_data = [];

    // Relationships

    public function array_values() {

        return $this->hasMany('Sukohi\EloquentArray\EloquentArrayItem', 'parent_id', 'id');

    }

    public function getArray($key) {

        $this->loadEloquentArray();
        return array_get($this->eloquent_array_data, $key, []);

    }

    public function getAllArray() {

        $this->loadEloquentArray();
        return $this->eloquent_array_data;

    }

    public function setArray($key, $values) {

        $this->loadEloquentArray();
        $this->eloquent_array_data[$key] = $values;

    }

    public function setAllArray($data) {

        $this->loadEloquentArray();
        $this->eloquent_array_data = $data;

    }

    public function unsetArray($key) {

        $this->loadEloquentArray();
        unset($this->eloquent_array_data[$key]);

    }

	public function saveArray() {

        $this->loadEloquentArray();

	    if(empty($this->id)) {

	        return false;

        }

	    $data = $this->eloquent_array_data;
		$this->clearArray();

		foreach($data as $key => $values) {

			foreach ($values as $value) {

				$array_item = new EloquentArrayItem([
					'model' => __CLASS__,
					'parent_id' => $this->id,
					'key' => $key,
					'value' => $value
				]);
				$array_item->save();

			}

		}

        $this->eloquent_array_data = $data;
        return true;

	}

	public function deleteArray($key) {

	    $this->unsetArray($key);
        return EloquentArrayItem::where('parent_id', $this->id)
            ->where('key', $key)
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

	public function scopeWhereArray($query, $key, $operator = null, $value = null, $boolean = 'and') {

		$ids = EloquentArrayItem::distinct()
			->where('key', $key)
			->where('model', __CLASS__)
			->where('value', $operator, $value)
			->lists('parent_id');
		return $query->whereIn('id', $ids, $boolean);

	}

	public function scopeOrWhereArray($query, $column, $operator = null, $value = null) {

		return $this->scopeWhereArray($query, $column, $operator, $value, 'or');

	}

	private function loadEloquentArray() {

	    if(!$this->eloquent_array_init_flag) {

            $this->eloquent_array_init_flag = true;
            $this->load('array_values');
            $this->eloquent_array_data = [];

            if($this->array_values->count() > 0) {

                foreach ($this->array_values as $item) {

                    $this->eloquent_array_data[$item->key][] = $item->value;

                }

            }

        }

    }

}