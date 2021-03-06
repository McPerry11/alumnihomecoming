<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Guest;
use App\Ticket;
use Carbon\Carbon;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->ajax()){
            return view('dashboard');
        } else {
            $confirmed = Guest::where('raffle', '=', '1')
            ->pluck('id');
            $tickets = Ticket::whereIn('guest_id', $confirmed)
            ->pluck('ticket_no');
            return $tickets;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('register');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $guest = new Guest;
        $guest->fill($request->only([
            'batch_year',
        ]));
        $guest->first_name = strtoUpper($request->first_name);
        $guest->last_name = strtoUpper($request->last_name);
        $guest->middle_name = strtoUpper($request->middle_name);
        $guest->created_at = Carbon::now('+8:00');
        $guest->updated_at = Carbon::now('+8:00');

        $guest->save();

        $ticket = new Ticket;
        $ticket->fill($request->only([
            'ticket_no',
        ]));
        $ticket->guest_id = $guest->id;

        $ticket->save();
        
        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $guest = Guest::find($id);
        if ($guest->birth_date != null) {
            $guest->birth_date = Carbon::parse($guest->birth_date)->isoFormat('MMMM D, YYYY');
        }
        return $guest;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $guest = Guest::find($id);
        if ($guest->birth_date != null) {
        	$guest->birth_date = Carbon::parse($guest->birth_date)->isoFormat('MMMM D, YYYY');
        }
        $tickets = Ticket::where('guest_id', '=', $guest->id)->get();
        return view('fillup', [
            'guest' => $guest,
            'ticket' => $tickets[0],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $guest = Guest::find($id);
        $guest->fill($request->only([
            'first_name',
            'last_name',
            'middle_name',
            'batch_year',
            'honors',
            'profession',
            'company_org',
            'address',
            'residence',
            'telephone',
            'cellphone',
            'email',
            'degree',
        ]));

        $ticket = Ticket::where('guest_id', '=', $id)->get();
        $ticket[0]->fill($request->only([
          'ticket_no',
      ]));

        $ticket[0]->save();

        if ($request->birth_date != null) {
            $date = Carbon::createFromIsoFormat('MMMM DD, YYYY', $request->birth_date, 'UTC');
            $guest->birth_date = $date->isoFormat('YYYY-M-DD');
        }
        $guest->updated_at = Carbon::now('+8:00');

        $guest->raffle = 1;
        $guest->save();

        return redirect('fillup');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $guest = Guest::find($id);
        $guest->delete();
        return redirect('dashboard');
    }
}
