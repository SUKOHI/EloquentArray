<?php namespace Sukohi\EloquentArray;

use \Sukohi\EloquentArray;

trait EloquentArrayTrait {

	public function saveArrayItem($keys = null) {

		if(is_null($keys)) {

			$keys = array_keys($this->casts);

		} else if(!is_array($keys)) {

			$keys = [$keys];

		}

		$this->deleteArrayItem($this->id);

		foreach ($keys as $key) {

			foreach ($this->$key as $value) {

				$array_item = new EloquentArrayItem([
					'model' => __CLASS__,
					'parent_id' => $this->id,
					'key' => $key,
					'value' => $value
				]);
				$array_item->save();

			}

		}

	}

	public function deleteArrayItem($id) {

		return EloquentArrayItem::where('parent_id', $id)
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

	public static function refreshArray($keys) {

		if(!is_array($keys)) {

			$keys = [$keys];

		}

		foreach ($keys as $key) {

			EloquentArrayItem::where('key', $key)
				->where('model', __CLASS__)
				->delete();

			$items = with(new self)->select('id', $key)->get();

			if($items->count() > 0) {

				foreach ($items as $item) {

					foreach ($item->$key as $value) {

						$new_item = new EloquentArrayItem;
						$new_item->model = __CLASS__;
						$new_item->parent_id = $item->id;
						$new_item->key = $key;
						$new_item->value = $value;
						$new_item->save();

					}

				}

			}

		}

	}

}