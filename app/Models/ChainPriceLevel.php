<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChainPriceLevel extends Model
{
    protected $table = 'chain_price_levels';

    public static function getById($id)
    {
        $level = self::query()->find($id);
        return $level;
    }

    /**
     * Add price level
     *
     * @param $level
     * @param $chain
     * @return ChainPriceLevel|array
     */
    public static function add($level, $chain)
    {
        $priceLevel = new self();
        $priceLevel->level = $level;
        $priceLevel->chain_id = $chain;
        if ($priceLevel->save()) {
            return $priceLevel;
        }
        return [];
    }

    /**
     * Edit price level
     *
     * @param $id
     * @param $level
     * @param $chain
     * @return array|\Illuminate\Database\Eloquent\Collection|Model|null|static|static[]
     */
    public static function edit($id, $level, $chain)
    {
        $priceLevel = self::getById($id);
        if ($priceLevel) {
            $priceLevel->level = $level;
            $priceLevel->chain_id = $chain;
            if ($priceLevel->save()) {
                return $priceLevel;
            }
        }
        return [];
    }

    /**
     * Delete levels except ids
     *
     * @param $levelIds
     * @param $chainId
     */
    public static function deleteExceptIds($levelIds,$chainId) {
        $levels=self::where('chain_id',$chainId)->whereNotIn('id',$levelIds)->delete();
    }
}
