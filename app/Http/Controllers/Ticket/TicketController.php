<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller {

    public function index(Request $request) {
        
        $query = Ticket::query();

        $query->orderByRaw("status = 'open' desc")->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if (!empty($request->question_id)) {
            $query->where('question_id', $request->question_id);
        }

        if (!empty($request->user_id)) {
            $query->where('user_id', $request->user_id);
        } elseif (Auth::user()->role == 'student') {
            $query->where('user_id', Auth::id());
        }

        return view('app.Ticket.list-tickets', [
            'tickets' => $query->paginate(10),
        ]);
    }

    public function store(Request $request) {

        $ticket = new Ticket();
        $ticket->description = $request->description;
       
        if (!empty($request->question_id)) {
            $ticket->question_id = $request->question_id;
        }

        if (!empty($request->user_id)) {
            $ticket->user_id = $request->user_id;
        } else {
            $ticket->user_id = Auth::user()->id;
        } 

        $assets = [];

        if ($request->hasFile('assets')) {
            foreach ($request->file('assets') as $file) {

                $path = $file->store('tickets');
                $mime = $file->getMimeType();
                $type = explode('/', $mime)[0];

                $assets[] = [
                    'url'           => asset('storage/'.$path),
                    'type'          => $type,
                    'original_name' => $file->getClientOriginalName(),
                ];
            }
        }

        if (!empty($assets)) {
            $ticket->assets = json_encode($assets);
        }

        if ( $ticket->save()) {
            return redirect()->back()->with('success', 'Ticket criado com sucesso!');
        }

        return redirect()->back()->with('success', 'Ticket created successfully.');
    }

    public function update(Request $request, $id) {

        $ticket = Ticket::find($id);
        if (!$ticket) {
            return redirect()->back()->with('infor', 'Ticket não localizado!');
        }

        $ticket->answer = $request->answer;
        $ticket->status = $request->status;

        if ($ticket->save()) {
            return redirect()->back()->with('success', 'Ticket atualizado com sucesso!');
        }

        return redirect()->back()->with('error', 'Erro ao atualizar o ticket.');
    }
    
}
