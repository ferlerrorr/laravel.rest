<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use JWTAuth;

class MeetingController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('jwt.auth' , ['only'=>['update','store','destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //GET
    //Display [] of meetings
    public function index()
    {
        $meetings = Meeting::all();
        foreach($meetings as $meeting){
          $meeting->view_meeting = [
             'href' => 'api/v1/meeting/' . $meeting->id,
              'method' => 'GET'
            ];
        }

        $response = [
            'msg' => ' List of all meetings',
            'meeting' => $meetings
        ];

        return response()->json($response,200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //Post 
    //new meeting
    public function store(Request $request)
    {
        $this->validate($request,[
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
        ]);

        if(! $user = JWTAuth::parseToken()->authenticate()){

            return response()->json(['msg' => 'User not found'],404);

        };

        //
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = new Meeting ([
            'title' => $title,
            'description' => $description,
            'time' => Carbon::createFromFormat('YmdHie', $time)
        ]);

        //if OK
        if($meeting->save()){
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href'=>'api/v1/meeting' . $meeting->id,
                'method' => 'GET'
            ];
            $response = [
                'msg' => 'Meeting created',
                'meeting' => $meeting
            ];
    
            return response()->json($response,201);
        }

        //else
        $response = [
            'msg' => 'An Error has occured',
        ];
        return response()->json($response,400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();
       $meeting->view_meeting =[
        'href'=>'api/v1/meeting' . $meeting->id,
        'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting Information',
            'meeting' => $meeting
        ];
        return response()->json($response,200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //Post 
    //update meeting
    public function update(Request $request, $id)
    {
        $this->validate($request,[
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie',
        ]);

        if(!$user = JWTAuth::parseToken()->authenticate()){

            return response()->json(['msg' => 'User not found'],404);

        };

        //
        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;
        $meeting = [
            'title' => $title,
            'description' => $description,
            'time' => $time,
            'user_id' => $user_id,
            'view_meeting' => [
                'href' => 'api/v1/meeting/1',
                'method' => 'GET'
            ]
        ];

        //search user id
        // $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();

        $meeting = Meeting::with('users')->findOrFail($id);

        //if fail
        if(!$meeting->users()->where('users.id', $user->id)->first()){

            return response()->json(['msg'=>'user not registered for meeting, update not succeslful'],401);

        };

        //esleif OK Update
        $meeting->time = Carbon::createFromFormat('YmdHie',$time);
        $meeting->title = $title;
        $meeting->description = $description;
        //if update fail
            if(!$meeting->update()){

                return response()->json(['msg'=>'Error in updating, update not succeslful'],401);

            }

        $meeting->view_meeting = [
                'href' => 'api/v1/meeting/1',
                'method' => 'GET'
        ];



        $response = [
            'msg' => 'Meeting updated',
            'meeting' => $meeting
        ];

        return response()->json($response,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {    
        //search meeting id
        $meeting = Meeting::findorFail($id);
        ## OR
        ## $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();->firstOrFail();
        

        if(!$user = JWTAuth::parseToken()->authenticate()){

            return response()->json(['msg' => 'User not found'],404);

        };

        if(!$meeting->users()->where('users.id', $user->id)->first()){

            return response()->json(['msg'=>'user not registered for meeting, update not succeslful'],401);

        };
        
        //loop thru all users in meeting
        $users = $meeting->users;

        //detach all users in meeting
        $meeting->users()->detach();

        //if fail attach all users in meeting again
        if(!$meeting->delete()){
            foreach($users as $user){
                $meeting->users()->attach($user);
            }
            return response()->json(['msg'=>'deletion failed'],400);
        }

        //if ok
        $response = [
            'msg' => 'Meeting deleted',
            'create' => [
                'href' => 'api/v1/meeting',
                'method' => 'POST',
                'params' => 'title , description ,time'
            ]
        ];

        return response()->json($response,200);
    }
}
