<?php

namespace App\Database\Repositories;

use Framework\System\Auth;
use Framework\Http\Request;
use Framework\System\Encryption;
use Framework\Database\Repository;

class Users extends Repository
{
    /**
     * name of table
     *
     * @var string
     */
    public $table = 'users';

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
     * retrieves user by email
     *
     * @param  string $email
     * @return mixed
     */
    public function findOneByEmail(string $email)
    {
        return $this->findOneBy('email', $email);
    }
    
    /**
     * retrieves user by token
     *
     * @param  \App\Database\Repositories\Tokens $tokens
     * @param  string $token
     * @return mixed
     */
    public function findOneByToken(Tokens $tokens, string $token)
    {
        $token = $tokens->findOneByToken($token);

        if (!$token) {
            return false;
        }

        return $this->findOneByEmail($token->email);
    }
    
    /**
     * retrieves users by email
     *
     * @param  string $email
     * @return array
     */
    public function findAllByEmail(string $email): array
    {
        return $this->findAllBy('email', 'like', $email);
    }
    
    /**
     * retrieves users by company
     *
     * @return array
     */
    public function findAllByCompany(): array
    {
        return $this->select()
            ->where('id', '!=', Auth::get('id'))
            ->subQuery(function ($query) {
                if (!Auth::role(0)) {
                    $query->and('company', Auth::get('company'));
                }
            })
            ->all();
    }
    
    /**
     * retrieves users by role
     *
     * @param  string $role
     * @return array
     */
    public function findAllByRole(string $role): array
    {
        return $this->select()
            ->where('id', '!=', Auth::get('id'))
            ->and('role', $role)
            ->subQuery(function ($query) {
                if (!Auth::role(0)) {
                    $query->and('company', Auth::get('company'));
                }
            })
            ->all();
    }
    
    /**
     * retrieves all users except authenticate
     *
     * @param  int $items_per_pages
     * @return \Framework\Support\Pager
     */
    public function findAllPaginate(int $items_per_pages = 10): \Framework\Support\Pager
    {
        return $this->select()
            ->where('id', '!=', Auth::get('id'))
            ->subQuery(function ($query) {
                if (!Auth::role(0)) {
                    $query->and('company', Auth::get('company'));
                }
            })
            ->oldest()
            ->paginate($items_per_pages);
    }

    /**
     * store user
     *
     * @param  \Framework\Http\Request $request
     * @return int
     */
    public function store(Request $request): int
    {
        $role = Auth::role(0) ? $request->role : Roles::ROLE[2];

        return $this->insert([
            'parent_id' => $role === Roles::ROLE[2] ? Auth::get('id') : null,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company' => Auth::role(0) ? $request->company : Auth::get('company'),
            'password' => pwd_hash($request->password),
            'status' => 1,
            'role' => $role,
            'lang' => Auth::get('lang'),
            'country' => Auth::get('country')
        ]);
    }

    /**
     * store user from signup form
     *
     * @param  \Framework\Http\Request $request
     * @return int
     */
    public function register(Request $request): int
    {
        return $this->insert([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'company' => $request->company,
            'country' => $request->country,
            'password' => pwd_hash($request->password),
        ]);
    }
    
    /**
     * update user
     *
     * @param  \Framework\Http\Request $request
     * @param  int $id
     * @return bool
     */
    public function refresh(Request $request, int $id): bool
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'company' => $request->company,
            'country' => $request->country,
            'status' => $request->account_state
		];
		
		if ($request->filled('password')) {
			$data['password'] = pwd_hash($request->password);
		}

        return $this->updateIfExists($id, $data);
    }
    
    /**
     * delete user
     *
     * @param  \Framework\Http\Request $request
     * @param  int $id
     * @return bool
     */
    public function flush(Request $request, ?int $id = null): bool
    {
        return is_null($id) 
            ? $this->deleteBy('id', 'in', explode(',', $request->items))
            : $this->deleteIfExists($id);
    }

    /**
     * update user settings
     *
     * @param  \Framework\Http\Request $request
     * @param  int $id
     * @return bool
     */
    public function updateSettings(Request $request, int $id): bool
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'country' => $request->country,
            'address' => $request->address,
            'company' => $request->company,
            'phone' => $request->phone,
            'two_steps' => $request->has('two_steps') ? 1 : 0,
            'lang' => $request->lang,
            'timezone' => $request->timezone,
            'dark' => $request->has('dark') ? 1 : 0,
            'alerts' => $request->has('alerts') ? 1 : 0,
            'email_notifications' => $request->has('email_notifications') ? 1 : 0
		];
		
		if ($request->has('password')) {
			$data['password'] = pwd_hash($request->password);
		}

        if (!Auth::role(2)) {
            $users = $this->findAllBy('parent_id', $id);

            foreach ($users as $user) {
                $this->updateIfExists($user->id, ['company' => $request->company]);
            }
        }
        
        return $this->updateIfExists($id, $data);
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
            
                $query->and('id', '!=', Auth::get('id'));
                
                if (!Auth::role(0)) {
                    $query->and('company', Auth::get('company'));
                }
            })
            ->oldest()
            ->all();
    }
}
