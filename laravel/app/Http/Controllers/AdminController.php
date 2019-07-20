<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserUpload;
use App\Events\FileChanged;
use App\Models\Role;
use App\Models\UserData;
use App\Models\UserRole;

class AdminController extends Controller
{
    // All profile functions require admin authentication
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->middleware('bindings');
    }

    // List of users
    function userList(Request $request)
    {
        $search = $request->query('search');
        $roleId = $request->query('role');

        if (!empty($search) and !empty($roleId))
        {
            $users = User::whereHas('roles', function($query) use($roleId)
            {
                $query->where('role_id','=', $roleId);
            })
            ->where(function($query) use($search)
            {
                $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            })->paginate(100);
        }
        else if (!empty($search) and empty($roleId))
        {
            if(is_numeric($search))
            {
                $users = [User::find($search)];
            }
            else
            {
                $users = User::where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->paginate(100);
            }
        }
        else if (empty($search) and !empty($roleId))
        {
            $users = User::whereHas('roles', function($query) use($roleId)
            {
                $query->where('role_id','=', $roleId);
            })->paginate(100);
        }
        else
        {
            $users = User::paginate(100);
        }

        return view('pages/admin/user-list', compact('users'));
    }

    // View an indivual user profile
    function userProfile(User $user)
    {
        $roles = Role::get();
        $roleNames = [];

        foreach($roles as $role)
        {
            $roleNames[$role->name] = $role->name;
        }

        return view('pages/admin/user-profile', compact('user', 'roleNames'));
    }

    // View an indivual user profile
    function userProfileEdit(User $user)
    {
        $roles = Role::get();
        $roleNames = [];

        foreach($roles as $role)
        {
            $roleNames[$role->name] = $role->name;
        }

        return view('pages/admin/edit-user-profile', compact('user', 'roleNames'));
    }

    // Update user, userData, userRole
    function userEdit(User $user, Request $request)
    {
        
        $roles = $request->get('roles');

        if($roles)
        {
            UserRole::clear($user);
            UserRole::assign($user, $roles);
        }

        $allRequestFields = $request->all();

        if(isset( $allRequestFields['name']) )
        {
            $user->name = $allRequestFields['name'];
            $user->save();
        }

        if(isset( $allRequestFields['email'] ))
        {
            $user->email = $allRequestFields['email'];
            $user->save();
        }
        
        $userData = $user->data;
        
        //make sure user data exists
        if(isset($userData))
        {
            $userData->fill($allRequestFields);
            $userData->save();
        }
        else
        {
            $userData = new UserData();
            $userData->user_id = $user->id;
            $userData->fill($allRequestFields);
            $userData->save();
        }

        $request->session()->flash('success', 'User '.$user->email.' has been updated');
        return redirect('/user/' . $user->id );
    }

    // List of uploaded files
    function uploadList()
    {
        $uploads = UserUpload::paginate(100);
        return view('pages/admin/upload-list', compact('uploads'));
    }

    // Update information about an uploaded file
    function uploadEdit(UserUpload $upload, Request $request)
    {
        $upload->status = $request->get('status');
        $upload->save();

        // A map of statuses to user roles
        $statusMap =
        [
            'approved-medical' => 'medical',
            'approved-fire' => 'fire',
            'approved-ranger' => 'ranger'
        ];

        // If an admin sets an uploaded document to be approved
        if(isset($statusMap[$upload->status]))
        {
            // Assign them to their approved role
            UserRole::assign($upload->user, $statusMap[$upload->status]);
        }

        event(new FileChanged($upload));

        return;
    }
}
