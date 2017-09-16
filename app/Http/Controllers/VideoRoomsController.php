<?php

namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Twilio\Rest\Client;
    use Twilio\Jwt\AccessToken;
    use Twilio\Jwt\Grants\VideoGrant;

class VideoRoomsController extends Controller
{
    protected $sid;
    protected $token;
    protected $key;
    protected $secret;

    public function __construct()
    {
        $this->sid = config('services.twilio.sid');
        $this->token = config('services.twilio.token');
        $this->key = config('services.twilio.key');
        $this->secret = config('services.twilio.secret');
    }

    public function index()
    {
        $rooms = [];
        try {
            $client = new Client($this->sid, $this->token);
            $allRooms = $client->video->rooms->read([]);

            $rooms = array_map(function($room) {
                return $room->uniqueName;
            }, $allRooms);

        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        return view('index', ['rooms' => $rooms]);
    }

    public function createRoom(Request $request)
    {
        $client = new Client($this->sid, $this->token);

        $exists = $client->video->rooms->read([ 'uniqueName' => $request->roomName]);

        if (empty($exists)) {
            $client->video->rooms->create([
                'uniqueName' => $request->roomName,
                'type' => 'group',
                'recordParticipantsOnConnect' => false
            ]);

            \Log::debug("created new room: ".$request->roomName);
        }

        return redirect()->action('VideoRoomsController@joinRoom', [
            'roomName' => $request->roomName
        ]);
    }

    public function joinRoom($roomName)
    {
        // A unique identifier for this user
        //$identity = \Auth::user()->name;
        $identity = rand(3,1000);

        \Log::debug("joined with identity: $identity");
        $token = new AccessToken($this->sid, $this->key, $this->secret, 3600, $identity);

        $videoGrant = new VideoGrant();
        $videoGrant->setRoom($roomName);

        $token->addGrant($videoGrant);

        return view('room', [ 'accessToken' => $token->toJWT(), 'roomName' => $roomName ]);
    }
}
