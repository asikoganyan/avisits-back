<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'title',
        'description'
    ];
    protected $hidden = [
        'chain_id'
    ];
    public function fillableFields()
    {
        return $this->fillable;
    }

    public static function getAll($chainId,$filter = null) {
        $query = Position::where(['chain_id'=>$chainId]);
        $total = $query->count();
        if($filter!== null){
            if(isset($filter['pagination']) && count($filter['pagination']) > 0){
                $pagination = $filter['pagination'];
                if(isset($pagination['page']) && isset($pagination['perpage'])){
                    $page = (integer)$pagination['page'];
                    $perpage = (integer)$pagination['perpage'];
                    $query->offset(($page - 1) * $perpage)
                        ->limit($perpage);
                }
            }
            if(isset($filter['sort']) && count($filter['sort']) > 0){
                $sort = $filter['sort'];
                if(isset($sort['field']) && isset($sort['sort'])){
                    if(in_array($sort['field'],(new self)->getFillable()) || $sort['field'] == 'id'){
                        $query->orderBy($sort['field'], $sort['sort']);
                    }
                }
                else{
                    $query->orderBy("id", "desc");
                }
            }
            if(isset($filter['query']) && !empty($filter['sort'])){
                dd();
                $fQuery = $filter['query'];
                if(isset($fQuery['generalSearch']) && !empty($fQuery['generalSearch'])){
                   $query->where(function($query) use ($fQuery){
                       $query->orWhere('title','like','%'.$fQuery['generalSearch'].'%')
                           ->orWhere('description','like','%'.$fQuery['generalSearch'].'%');
                   });

                }
            }
        }
        return ["total"=>$total, 'position'=>$query->get()];
    }
}
