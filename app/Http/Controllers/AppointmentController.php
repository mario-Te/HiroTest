<?php

namespace App\Http\Controllers;
use Google\Client;
use Google\Service\Calendar as GoogleCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Carbon\Carbon;
use App\Models\User;


class AppointmentController extends Controller
{
    private $googleClient;

    public function __construct(Client $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    public function store(Request $request)
    {
        // Get the authenticated user (doctor)
        $doctor = Auth::user();

        // Authenticate with Google
        $this->authenticateWithGoogle($doctor->email);

        // Create a new event on the Google Calendar/
        $start_datetime_String =$request->input('start');
        $start_date_time = Carbon::createFromFormat('Y-m-d\TH:i', $start_datetime_String);
        $formattedStartDatetime = $start_date_time->format('Y-m-d\TH:i:sP');
        $end_datetime_String =$request->input('end');
        $end_date_time = Carbon::createFromFormat('Y-m-d\TH:i', $end_datetime_String);
        $formattedEndDatetime = $end_date_time->format('Y-m-d\TH:i:sP');
        
        $event=new GoogleCalendar\Event(array(
                        'summary' => $request->input('summary'),
                        'location' =>$doctor->email,
                        'start' => array(
                            'dateTime' => $formattedStartDatetime,
                            'timeZone' => 'America/Los_Angeles',
                        ),
                        'end' => array(
                            'dateTime' => $formattedEndDatetime,
                            'timeZone' => 'America/Los_Angeles',
                        ),
                        'creator' => [
                             'email' => $doctor->email, // Set the creator's email as the doctor's email
                           ],  
                        ));
        $calendarService = new GoogleCalendar($this->googleClient);
        $calendarService->events->insert('primary', $event);

        // Return a response indicating success
        return redirect()->back()->with('success', 'Appointment created successfully');
    }

   public function update(Request $request, $id)
{
    // Get the authenticated user (doctor)
    $doctor = Auth::user();

    // Authenticate with Google
    $this->authenticateWithGoogle($doctor->email);
    $convertedDatStart = \Carbon\Carbon::parse($request->input('start'))->format('Y-m-d\TH:i:s\Z');  
    $convertedDateEnd = \Carbon\Carbon::parse($request->input('end'))->format('Y-m-d\TH:i:s\Z');  
    // Retrieve the event from the Google Calendar
    $calendarService = new GoogleCalendar($this->googleClient);
    $event = $calendarService->events->get('primary', $id);
    // Update the event properties
    $event->setSummary($request->input('summary'));
    $end = new GoogleCalendar\EventDateTime();
    $end->setDateTime($convertedDateEnd);
    $event->setEnd($end);
    $start = new GoogleCalendar\EventDateTime();
    $start->setDateTime($convertedDatStart);
    $event->setStart($start);
    // Save the updated event
    $calendarService->events->update('primary', $event->getId(), $event);

    // Return a response indicating success
    return response()->json(['message' => 'Appointment updated successfully']);
}
  public function checkAuthorization($eventId)
    {
        $doctor = Auth::user();

    // Authenticate with Google
    $this->authenticateWithGoogle($doctor->email);
            $calendarService = new GoogleCalendar($this->googleClient);
            $event = $calendarService->events->get('primary', $eventId);
    
               
                 if ($event->getLocation()===Auth::user()->email) {
                        return response()->json(['authorized' => true]);
                    } else {
                        return response()->json(['authorized' => false]);
                    }
    }
    public function destroy($id)
    {
        
        $doctor = Auth::user();
        // Authenticate with Google
        $this->authenticateWithGoogle($doctor->email);

        // Delete the event from the Google Calendar
        $calendarService = new GoogleCalendar($this->googleClient);
        $calendarService->events->delete('primary', $id);

        // Return a response indicating success
        return redirect()->back()->with('success', 'Appointment deleted successfully');
        
    }

   public function index()
{
  
        return view('calendar');
}
  public function getEvents()
  {
// Get the authenticated user (doctor)
    $current_user = Auth::user();
    $doctor="";
    if($current_user->isDoctor)
        $doctor=$current_user;
    else {
        $Id=$current_user->Doctor_Id;
        $doctor= User::find($Id);
    }
    // Authenticate with Google
    $this->authenticateWithGoogle($doctor->email);

    // Retrieve a list of events from the Google Calendar
    $calendarService = new GoogleCalendar($this->googleClient);
   
   $events = $calendarService->events->listEvents('primary');
    

    // Process the events and extract relevant information
    $appointments = [];
    foreach ($events->getItems() as $event) {
        if ($event->getLocation()===$doctor->email)
        $appointments[] = [
            'id' => $event->getId(),
            'summary' => $event->getSummary(),
            'start' => $event->getStart()->getDateTime(),
            'end' => $event->getEnd()->getDateTime(),
        ];
    }

    // Return the list of appointments
     return response()->json($appointments);
  }
   private function authenticateWithGoogle($doctorEmail)
{
    $this->googleClient->setClientId('513809227710-n3lp4vp7jp84mrgmq1qomt55ncj8id95.apps.googleusercontent.com');
    $this->googleClient->setClientSecret('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');
    $this->googleClient->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
    $this->googleClient->setAccessToken('AIzaSyD_mBzKaq1E4pwoFtI-GYIH_NItdEX6xlE');
    $this->googleClient->setAuthConfig(storage_path('app/google-calendar/service-account-credentials.json'));
    $this->googleClient->setScopes([
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.events',
    ]);

    // Retrieve the authenticated user (doctor)
    $doctor = Auth::user();

    // Set the access token and refresh token if available
    $accessToken = $doctor->token;
    $refreshToken = $doctor->refresh_token;

    if ($accessToken && $refreshToken) {
        $this->googleClient->setAccessToken($accessToken);

        // Check if the access token is expired
        if ($this->googleClient->isAccessTokenExpired()) {
            // Set the refresh token to obtain a new access token
            $this->googleClient->setRefreshToken($refreshToken);

            // Fetch the access token using the refresh token
            $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);

            // Update the access token in the database
            $doctor->update(['token' => $this->googleClient->getAccessToken()]);
        }
    }
}
}
