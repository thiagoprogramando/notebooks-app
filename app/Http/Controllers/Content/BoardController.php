<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller {
    
    public function index(Request $request) {

        $query = Board::orderBy('name', 'asc');

        if (!empty($request->input('name'))) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if (!empty($request->input('state'))) {
            $query->where('state', $request->input('state'));
        }

        if (!empty($request->input('city'))) {
            $query->where('city', $request->input('city'));
        }

        if (!empty($request->input('code'))) {
            $query->where('code', $request->input('code'));
        }

        return view('app.Content.Board.list-boards', [
            'boards' => $query->paginate(30),
        ]);
    }

    public function store(Request $request) {
        
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'city'  => ['nullable', 'string', 'max:255'],
            'code'  => ['nullable', 'string', 'max:50', 'unique:boards,code'],
        ]);

        $board = new Board();
        $board->name  = $validated['name'];
        $board->state = $validated['state'] ?? null;
        $board->city  = $validated['city'] ?? null;
        $board->code  = $validated['code'] ?? null;
        if ($board->save()) {
            return redirect()->back()->with('success', 'Banca criada com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao criar a banca. Tente novamente!');
    }

    public function update(Request $request, $id) {
        
        $board = Board::find($id);
        if (!$board) {
            return redirect()->back()->with('error', 'Banca não encontrada!');
        }

        $validated = $request->validate([
            'code'  => ['nullable', 'string', 'max:50', 'unique:boards,code,' . $board->id],
        ]);

        if (!empty($request->input('name'))) {
            $board->name = $request->input('name');
        }
        if (!empty($request->input('state'))) {
            $board->state = $request->input('state');
        } else {
            $board->state = null;
        }
        if (!empty($request->input('city'))) {
            $board->city = $request->input('city');
        } else {
            $board->city = null;
        }
        if (!empty($request->input('code'))) {
            $board->code = $request->input('code');
        }

        if ($board->save()) {
            return redirect()->back()->with('success', 'Banca atualizada com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao atualizar a banca. Tente novamente!');
    }
}
