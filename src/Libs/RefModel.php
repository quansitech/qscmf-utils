<?php
namespace Qscmf\Utils\Libs;

class RefModel{

    protected $model;
    protected $ref_id;

    protected $model_ents;

    public function __construct($model, $ref_id = 'id')
    {
        $this->model = $model;
        $this->ref_id = $ref_id;
    }

    public function fill($data_ents, $key){
        $keys = collect($data_ents)->pluck($key)->unique()->all();
        $fields = join(',', $this->model->getDbFields());
        if(!$keys){
            $this->model_ents = [];
            return;
        }
        $this->model_ents = $this->model->where([$this->ref_id => ['in', $keys]])->getField($this->ref_id . ',' . $fields, true);
    }

    public function pick($value, $field = null, $callback = null){

        if(is_null($field)){
            $pick_data = $this->model_ents[$value];
        }
        else{
            $pick_data = $this->model_ents[$value][$field];
        }

        if(!is_null($callback)){
            return call_user_func($callback, $pick_data);
        }

        return $pick_data;
    }

    public function pickAll(){
        return $this->model_ents;
    }

}