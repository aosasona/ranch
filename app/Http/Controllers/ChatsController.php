<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Models\Chatslist;

use \App\Models\Chats;
use App\Models\User_status;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;

class ChatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Show all of the user's chats
        $currentUser = Cookie::get('username');

        $chats = Chatslist::where('user1', $currentUser)->orwhere('user2', $currentUser)->orderBy('unread_count', 'DESC')->get();

        return view('chat.index')->with('chats', $chats)
            ->with('currentUser', $currentUser);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('error');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        return view('error');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($user)
    {
        //Show current conversation

        $currentUser = Cookie::get('username');

        if (strtolower($user) === strtolower($currentUser)) {
            return Redirect::back(); //GO BACK IF THE USER IS THE OWNER OF THE CONVO
        }

        //GET THE ENTIRE CONVERSATION
        $chats = Chats::where(function ($query_a) use ($user, $currentUser) {
            $query_a->where('from', $user)
                ->where('to', $currentUser);
        })
            ->orwhere(function ($query_b) use ($user, $currentUser) {
                $query_b->where('to', $user)
                    ->where('from', $currentUser);
            })
            ->get();


        //UPDATE THE READ RECEIPT FOR THE CONVERSATION
        Chats::where(function ($query_a) use ($user, $currentUser) {
            $query_a
                ->where('from', $user)
                ->where('to', $currentUser)
                ->where('status', 'delivered');
        })
            ->orwhere(function ($query_b) use ($user, $currentUser) {
                $query_b
                    ->where('to', $user)
                    ->where('from', $currentUser)
                    ->where('status', 'delivered');
            })->update(['status' => 'read']);


        //UPDATE THE UNREAD COUNTERS FOR THE CONVERSATION
        Chatslist::where(function ($query_a) use ($user, $currentUser) {
            $query_a->where('user1', $user)
                ->where('user2', $currentUser)
                ->where('unread_count', '>', '0');
        })
            ->orwhere(function ($query_b) use ($user, $currentUser) {
                $query_b->where('user2', $user)
                    ->where('user1', $currentUser)
                    ->where('unread_count', '>', '0');
            })->update(['unread_count' => '0']);



        return view('chat.view')->with('chats', $chats)
            ->with('user', $user)
            ->with('currentUser', $currentUser);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('error');
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
        //
        return view('error');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($username)
    {
        //
        return view('error');
    }

    /**
     * Delete the entire conversation
     */
    public function deleteConvo($username)
    {
        $currentUser = Cookie::get('username');
        $user = $username;

        if ($currentUser && $user) {

            $checkChat = Chats::where(function ($query_a) use ($user, $currentUser) {
                $query_a->where('from', $user)
                    ->where('to', $currentUser);
            })
                ->orwhere(function ($query_b) use ($user, $currentUser) {
                    $query_b->where('to', $user)
                        ->where('from', $currentUser);
                })
                ->count();

            if ($checkChat !== 0) {

                Chats::where(function ($query_a) use ($user, $currentUser) {
                    $query_a->where('from', $user)
                        ->where('to', $currentUser);
                    })
                    ->orwhere(function ($query_b) use ($user, $currentUser) {
                        $query_b->where('to', $user)
                            ->where('from', $currentUser);
                    })
                    ->delete();

                return response('Deleted', 200);
            } else {
                return response('Nothing to delete mate', 400);
            }
        } else {
            return response('Lol, you can not do that.', 406);
        }
    }
}
