<?php
 
namespace App\Http\Controllers;
  
use Illuminate\Http\Request;
use App\Models\User;
  
class NotificationController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        return view('pushNotification');
    } 
  
     /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        // $firebaseToken = "eYLvDEurQ2So5zenFinIB_:APA91bGPSs_IyzO9IatM3bzlcbWWHBZ4jbDf0J-IYDBRlDw4_3yOU3QetHKCciCpgifF-z0ct58irc03bSY7a2dgph4KlXMk-nxnA9afyQ-Afl0e7LtZZxE7JTC17yR974rxDofOhxuM";

        $SERVER_API_KEY = env('FCM_SERVER_KEY');
    
        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,  
            ]
        ];
        $dataString = json_encode($data);
      
        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];
      
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
                 
        $response = curl_exec($ch);
        //  dd($response);

        $status_object = json_decode($response);

   

        if($status_object->success >= 1){
            /// success

            /**
             * Sample response:
             * "{
             *      "multicast_id":7994130162176097874,
             *      "success":1,
             *      "failure":0,
             *      "canonical_ids":0,
             *      "results":[
             *            {
             *              "message_id":"0:1682841970330223%4eec8825f9fd7ecd"
             *            }
             *      ]
             * }"
             */

            $results = $status_object->results[0]->message_id;
            $message = "Notification send successfully..\n Sample message ID(s): $results";
            return back()->with('success', $message);

        }
        else{
            /// failed
            /**
             * Sample response :
             * "{
             *      "multicast_id":7516479049241586463,
             *      "success":0,"failure":1,
             *      "canonical_ids":0,
             *      "results":[
             *          {
             *              "error":"MismatchSenderId"
             *          }
             *      ]
             *  }" 
             */
            $results = $status_object->results[0]->error;
            $message = "Failed to send Notification.\n Resulsting error(s) are: $results";
            return back()->with('success', $message);
        }
    
    
    }
}