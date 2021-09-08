<?php

namespace App\Database\Repositories;

use Framework\System\Auth;
use Framework\Http\Request;
use Framework\Database\Repository;

class Activities extends Repository
{
    /**
     * name of table
     *
     * @var string
     */
    public $table = 'activities';

    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct($this->table);
    }

    /**
     * retrieves all activites
     * 
     * @param  int $items_per_pages
     * @return \Framework\Support\Pager
     */
    public function findAllPaginate(int $items_per_pages = 10): \Framework\Support\Pager
    {
        return $this->select(['activities.id', 'user', 'url', 'ip_address', 'action', 'activities.created_at'])
            ->join('users', 'activities.user', '=', 'users.email')
            ->subQuery(function ($query) {
                if (!Auth::role(0)) {
                    $query->where('user', Auth::get('email'));
                        
                    if (Auth::role(1)) {
                        $query->or('users.parent_id', Auth::get('id'));
                    }
                }
            })
            ->oldest()
            ->paginate($items_per_pages);
    }
    
    /**
     * delete activities by id
     *
     * @param  \Framework\Http\Request $request
     * @return bool
     */
    public function deleteById(Request $request): bool
    {
        return $this->deleteBy('id', 'in', explode(',', $request->items));
    }
    
    /**
     * store
     *
     * @param  mixed $user
     * @param  string $action
     * @param  \Framework\Http\Request $request $request
     * @return int
     */
    public function store($user, string $action, Request $request): int
    {
        return $this->insert([
            'user' => is_null($user) ? Auth::get('email') : $user,
            'url' => $request->fullUri(),
            'method' => $request->method(),
            'ip_address' => $request->remoteIP(),
            'action' => $action
        ]);
    }
    
    /**
     * retrieves data from date range
     *
     * @param  mixed $start
     * @param  mixed $end
     * @return array
     */
    public function findAllDateRange($date_start, $date_end): array
    {
        return $this->select()
            ->subQuery(function($query) use ($date_start, $date_end) {
                if (!empty($date_start) && !empty($date_end)) {
                    $query->whereBetween('created_at', $date_start, $date_end);
                }
            })
            ->oldest()
            ->all();
    }
}
