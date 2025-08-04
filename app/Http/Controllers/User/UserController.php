<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {
    
    public function index(Request $request, $role) {
        
        $query = User::query();

        $query->orderBy('id', 'asc')->where('role', $role);

        if ($request->has('name')) {
            $query->where('name', 'LIKE', '%'.$request->name.'%');
        }

        if (!empty($request->cpfcnpj)) {
            $query->where('cpfcnpj', preg_replace('/[\.\-\/]/', '', $request->cpfcnpj));
        }

         if (!empty($request->email)) {
            $query->where('email', $request->email);
        }

        return view('app.User.list-users', [
            'users' => $query->paginate(10),
            'role'  => $role,
        ]);
    }

    public function show($id) {
        
        $user = User::find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Perfil não encontrado!');
        }

        return view('app.User.view-user', [
            'user' => $user,
        ]);
    }

    public function store(Request $request, $role) {
        
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'cpfcnpj'   => 'required|string|max:20',
        ]);

        $user = new User();
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->cpfcnpj  = preg_replace('/[\.\-\/]/', '', $request->cpfcnpj);
        $user->role     = $role;
        $user->password = bcrypt(preg_replace('/[\.\-\/]/', '', $request->cpfcnpj));
        if ($user->save()) {
            return redirect()->back()->with('success', 'Novo Perfil cadastrado com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível cadastrar o novo Perfil, verifique os dados e tente novamente!');
    }

    public function update(Request $request, $id) {

        $user = User::find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Perfil não encontrado!');
        }

        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->cpfcnpj  = preg_replace('/[\.\-\/]/', '', $request->cpfcnpj);
        $user->role     = $request->role;
        $user->address_postal_code     = $request->address_postal_code;
        $user->address_num             = $request->address_num;
        $user->address_address         = $request->address_address;
        $user->address_city             = $request->address_city;
        $user->address_state            = $request->address_state;
        if ($user->save()) {
            return redirect()->back()->with('success', 'Perfil salvo com sucesso!');
        }

        return redirect()->back()->with('error', 'Não foi possível atualizar o Perfil, verifique os dados e tente novamente!');
    }

    public function destroy($id) {
        
        $user = User::find($id);
        if ($user && $user->delete()) {
            return redirect()->back()->with('success', 'Perfil excluído com sucesso!');
        }

        return redirect()->back()->with('infor', 'Não foi possível Excluir/ou Encontrar o Perfil, verifique os dados e tente novamente!');
    }


}
