<?php
#app/Models/ShopSupplier.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSupplier extends Model
{
    public $timestamps = false;
    public $table = SC_DB_PREFIX.'shop_supplier';
    protected $guarded = [];
    private static $getList = null;
    protected $connection = SC_CONNECTION;

    protected  $sc_limit = 'all'; // all or interger
    protected  $sc_status = 1; // 1 - active, 0- inactive, all - all
    protected  $sc_paginate = 0; // 0: dont paginate,
    protected  $sc_type = 'all'; // all or interger,0 - banner, 1 - background
    protected  $sc_sort = [];
    protected  $sc_moreWhere = []; // more wehere
    protected  $sc_random = 0; // 0: no random, 1: random

    public static function getList()
    {
        if (self::$getList == null) {
            self::$getList = self::get()->keyBy('id');
        }
        return self::$getList;
    }

    public function products()
    {
        return $this->hasMany(ShopProduct::class, 'supplier_id', 'id');
    }


    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(function ($supplier) {
        });
    }

    /**
     * [getUrl description]
     * @return [type] [description]
     */
    public function getUrl()
    {
        return route('supplier.detail', ['alias' => $this->alias]);
    }

    /*
    *Get thumb
    */
    public function getThumb()
    {
        return sc_image_get_path_thumb($this->image);
    }

    /*
    *Get image
    */
    public function getImage()
    {
        return sc_image_get_path($this->image);

    }

//Scort
    public function scopeSort($query, $sortBy = null, $sortOrder = 'desc')
    {
        $sortBy = $sortBy ?? 'sort';
        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Get page detail
     *
     * @param   [string]  $key     [$key description]
     * @param   [string]  $type  [id, alias]
     *
     */
    public function getDetail($key, $type = null, $status = 1)
    {
        if(empty($key)) {
            return null;
        }
        if ($type == null) {
            $data = $this->where('id', (int) $key);
        } else {
            $data = $this->where($type, $key);
        }
        if ($status == 1) {
            $data = $data->where('status', 1);
        }
        return $data->first();
    }


    /**
     * Start new process get data
     *
     * @return  new model
     */
    public function start() {
        return new ShopSupplier;
    }

    /**
     * Set value limit
     */
    public function setLimit($limit) {
        if ($limit === 'all') {
            $this->sc_limit = $limit;
        } else {
            $this->sc_limit = (int)$limit;
        }
        return $this;
    }

    /**
     * Set status
     */
    private function setStatus($status) {
        if ($status === 'all') {
            $this->sc_status = $status;
        } else {
            $this->sc_status = (int)$status ? 1 : 0;
        }
        return $this;
    }

    /**
     * Set value sort
     */
    public function setSort($sort) {
        if(is_array($sort)) {
            $this->sc_sort[] = $sort;
        }
        return $this;
    }

    /**
     * Enable paginate mode
     */
    public function setPaginate() {
        $this->sc_paginate = 1;
        return $this;
    }

    /**
     * Set type
     */
    private function setType($type) {
        if ($type === 'all') {
            $this->sc_type = $type;
        } else {
            $this->sc_type = (int)$type;
        }
        return $this;
    }

    /**
     * Set random mode
     */
    public function setRandom() {
        $this->sc_random = 1;
        return $this;
    }

    /**
     * Add more where
     */
    public function setMoreWhere($moreWhere) {
        if(is_array($moreWhere)) {
            if(count($moreWhere) == 2) {
                $where[0] = $moreWhere[0];
                $where[1] = '=';
                $where[2] = $moreWhere[1];
            } elseif(count($moreWhere) == 3) {
                $where = $moreWhere;
            }
            if(count($where) == 3) {
                $this->sc_moreWhere[] = $where;
            }
        }
        return $this;
    }

    /**
     * Get banner
     */
    public function getBanner() {
        $this->setType(0);
        $this->setStatus(1);
        return $this;
    }

    /**
     * Get background
     */
    public function getBackground() {
        $this->setType(1);
        $this->setStatus(1);
        return $this;
    }

    /**
     * build Query
     */
    public function buildQuery() {
        $query = $this;
        if ($this->sc_status !== 'all') {
            $query = $query->where('status', $this->sc_status);
        }
        if ($this->sc_type !== 'all') {
            $query = $query->where('type', $this->sc_type);
        }

        if (count($this->sc_moreWhere)) {
            foreach ($this->sc_moreWhere as $key => $where) {
                if(count($where)) {
                    $query = $query->where($where[0], $where[1], $where[2]);
                }
            }
        }

        if ($this->sc_random) {
            $query = $query->inRandomOrder();
        } else {
            if (is_array($this->sc_sort) && count($this->sc_sort)) {
                foreach ($this->sc_sort as  $rowSort) {
                    if(is_array($rowSort) && count($rowSort) == 2) {
                        $query = $query->sort($rowSort[0], $rowSort[1]);
                    }
                }
            }
        }

        return $query;
    }

     /**
     * Get Query
     */
    public function getQuery() {
        $query = $this->buildQuery();
        if(!$this->sc_paginate) {
            if ($this->sc_limit !== 'all') {
                $query = $query->limit($this->sc_limit);
            }
        }
		return $query = $query->toSql();
    }

     /**
     * Get data
     */
    public function getData($action = []) {
        $query = $this->buildQuery();
        if($this->sc_paginate) {
            $data =  $query->paginate(($this->sc_limit === 'all') ? 20 : $this->sc_limit);
        } else {
            if ($this->sc_limit !== 'all') {
                $query = $query->limit($this->sc_limit);
            }
            $data  = $query->get();
            
            if (!empty($action['keyBy'])) {
                $data = $data->keyBy($action['keyBy']);
            }
            if (!empty($action['groupBy'])) {
                $data = $data->groupBy($action['groupBy']);
            }
        }
        return $data;
    }


}
