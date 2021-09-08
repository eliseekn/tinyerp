<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Report;
use App\Helpers\Activity;
use App\Helpers\Countries;
use Framework\Http\Request;
use Framework\Support\Alert;
use Framework\Routing\Controller;
use App\Http\Validators\UpdateUser;
use App\Database\Repositories\Roles;
use App\Database\Repositories\Users;
use App\Http\Validators\RegisterUser;

class UsersController extends Controller
{
    /**
     * @var \App\Database\Repositories\Users $users
     */
    private $users;
    
    /**
     * __construct
     *
     * @param  \App\Database\Repositories\Users $users
     * @return void
     */
    public function __construct(Users $users)
    {
        $this->users = $users;
    }

    /**
     * index
     *
     * @return void
     */
    public function index(): void
    {
        $data = $this->users->findAllPaginate();
        $customers = count($this->users->findAllByRole(Roles::ROLE[1]));
        $administrators = count($this->users->findAllByRole(Roles::ROLE[0]));
        $users = count($this->users->findAllByRole(Roles::ROLE[2]));

        $this->render('admin.users.index', compact('data', 'customers', 'administrators', 'users'));
    }

	/**
	 * new
	 * 
     * @param  \App\Database\Repositories\Roles $roles
	 * @return void
	 */
	public function new(Roles $roles): void
	{
        $roles = $roles->selectAll();
        $countries = Countries::all();
		$this->render('admin.users.new', compact('roles', 'countries'));
	}
	
	/**
	 * edit
	 * 
     * @param  \App\Database\Repositories\Roles $roles
	 * @param  int $id
	 * @return void
	 */
	public function edit(Roles $roles, int $id): void
	{
        $data = $this->users->findOne($id);
        $roles = $roles->selectAll();
        $countries = Countries::all();
        $this->render('admin.users.edit', compact('data', 'roles', 'countries'));
	}
	
	/**
	 * read
	 * 
	 * @param  int $id
	 * @return void
	 */
	public function read(int $id): void
	{
        $data = $this->users->findOne($id);
        $this->render('admin.users.read', compact('data'));
	}

	/**
	 * create
	 *
     * @param  \Framework\Http\Request $request
	 * @return void
	 */
	public function create(Request $request): void
	{
        RegisterUser::register()->validate($request->except('csrf_token'))->redirectOnFail();
	    $id = $this->users->store($request);

        Activity::log(__('user_created'));
        redirect()->route('users.read', $id)->withToast('success', __('user_created'))->go();
    }
    
	/**
	 * update
	 *
     * @param  \Framework\Http\Request $request
     * @param  int $id
	 * @return void
	 */
	public function update(Request $request, int $id): void
	{
		UpdateUser::register($id)->validate($request->except('csrf_token'))->redirectOnFail();
        $this->users->refresh($request, $id);
		
        Activity::log(__('user_updated'));
        redirect()->route('users.read', $id)->withToast('success', __('user_updated'))->go();
    }

	/**
	 * delete
	 *
     * @param  \Framework\Http\Request $request
     * @param  int|null $id
	 * @return void
	 */
	public function delete(Request $request, ?int $id = null): void
	{
        $this->users->flush($request, $id);

		if (!is_null($id)) {
            Activity::log(__('user_deleted'));
            redirect()->route('users.index')->withToast('success', __('user_deleted'))->go();
		} else {
            Activity::log(__('users_deleted'));
            Alert::toast(__('users_deleted'))->success();
            response()->json(['redirect' => route('users.index')]);
        }
	}

	/**
	 * export
	 *
     * @param  \Framework\Http\Request $request
	 * @return void
	 */
	public function export(Request $request): void
	{
		$data = $this->users->findAllDateRange($request->date_start, $request->date_end);
        $filename = 'users_' . date('Y_m_d_His') . '.' . $request->file_type;

        Activity::log(__('data_exported'));
        
		Report::generate($filename, $data, [
			'name' => __('name'), 
			'role' => __('role'), 
            'email' => __('email'),
            'phone' => __('phone'),
            'company' => __('company'),
            'address' => __('address'),
            'lang' => __('lang'),
            'country' => __('country'),
			'created_at' => __('created_at')
		]);
	}
}
