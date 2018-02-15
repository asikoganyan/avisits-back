<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price_level_id',
        'service_id',
        'price',
        'max_price',
        'from',
        'inactive',
        'created_at',
        'updated_at'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function getAll()
    {
        return self::with('level')->get();
    }

    public function level()
    {
        return $this->hasOne('App\Models\ChainPriceLevel', 'id', 'price_level_id');
    }

    /**
     * Get service price by id
     *
     * @param $id
     * @return mixed
     */
    public static function getById($id)
    {
        $servicePrice = self::find($id);
        return $servicePrice;
    }

    /**
     * Add new service price
     *
     * @param $priceId
     * @param $serviceId
     * @param $minPrice
     * @param $maxPrice
     * @param $date
     * @return ServicePrice|array
     */
    public static function add($priceId, $serviceId, $minPrice, $maxPrice, $date)
    {
        $servicePrice = new self();
        $servicePrice->price_level_id = $priceId;
        $servicePrice->service_id = $serviceId;
        $servicePrice->price = $minPrice;
        $servicePrice->max_price = $maxPrice;
        $servicePrice->from = $date;
        if ($servicePrice->save()) {
            return $servicePrice;
        }
        return [];
    }

    /**
     * Edit service price
     *
     * @param $id
     * @param $priceId
     * @param $serviceId
     * @param $minPrice
     * @param $maxPrice
     * @param $date
     * @return ServicePrice|array
     */
    public static function edit($id, $priceId, $serviceId, $minPrice, $maxPrice, $date)
    {
        $servicePrice = self::getById($id);
        if ($servicePrice) {
            $servicePrice->price_level_id = $priceId;
            $servicePrice->service_id = $serviceId;
            $servicePrice->price = $minPrice;
            $servicePrice->max_price = $maxPrice;
            $servicePrice->from = $date;
            if ($servicePrice->save()) {
                return $servicePrice;
            }
        }
        return [];
    }

    /**
     * Delete service prices except ids
     *
     * @param $serviceId
     * @param array $ids
     */
    public static function deleteExceptIds($serviceId, $ids = [])
    {
        self::where('service_id', $serviceId)->whereNotIn('id', $ids)->delete();
    }

    public static function getOne($params)
    {
        return self::select((new self)->getTable() . '.*')->join("price_levels", "price_levels.id", "=", "service_prices.price_level_id")
            ->where(["price_levels.chain_id" => $params["chain"], "service_prices.id" => $params['service_price']])
            ->first();
    }
}
