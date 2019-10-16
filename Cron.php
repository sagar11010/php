<?php 
//this is cron it automaticly called on some certain conditions


if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends CI_Controller 
{
    public function __construct() 
    {
        parent::__construct();
         $this->load->helper('url');
        $this->load->helper('string');
        $this->load->helper('api_helper');
        $this->load->model('webservice/Car_model', 'car');
        $this->input = jsonDecrypt();
        
    }

    // public function cronRaceReminder()
    // {   
    //     date_default_timezone_set(API_TIME_ZONE);
    //     $before15Min = date("H:i:s",strtotime("+15 minute"));
    //     // $currentTime=date("H:i:s",time());
    //     // print_r($t); die();
    //     $where = array(
    //                     'race_status'=>'0',
    //                     'race_time <='=> $before15Min,
    //                     'is_notification_send' =>'0'
    //                             );
    //     $getData = $this->car->getRows('tbl_create_race', '*', $where, 'TRUE');
    //     // $getData = $this->car->betweenDate($before15Min,$currentTime);
    //     // echo $this->db->last_query(); 

    //     foreach ($getData as $value) {

    //     $raceId   = $value->id;    
    //     $raceTime = $value->race_time; 
    //     $userId   = $value->user_id;
    //     $raceName = $value->race_name;
    //     $raceCode = $value->race_code;
    //     $raceTime = $value->race_time; 
    //     // echo "<pre>";
    //     // print_r($userId);
    //     // die();

    //       // =======================PUSH NOTIFICATION============================== //
    //                     //  BOC device push notification
                       
    //                     $userData2 = $this->car->getRows('tbl_users', '*', ['id'=> $userId], 'TRUE');
    //                     // echo "<pre>";
    //                     // print_r($userData2);
    //                      // die();

    //                         $notificationType = "6";
    //                         $notificationMessage = " Your ". $raceName ." is going to start in next 15 mins.";

    //                         $apnsArray = array(
    //                             "sound"=>"default",
    //                             "notificationType" => $notificationType,
    //                             "userId" => $userId,
    //                             "raceCode" => $raceCode,
    //                             "raceId" =>  $raceId,
    //                             "time" =>  $raceTime,
    //                             "alert" => $notificationMessage,
    //                             "body" => $notificationMessage
    //                         );
    //                     // echo"<pre>";
    //                     // print_r($apnsArray); 
    //                     // die();

    //                     $android_tokens = array();
    //                     $ios_tokens = array();

    //                     foreach ($userData2 as $k => $user) {

    //                         if ($user->device_type == '1') { //ios
    //                             $ios_tokens [$k] = $user->device_token;
    //                         } else if ($user->device_type == '2') {  //andriod
    //                             $android_tokens [$k] = $user->device_token;
    //                         }

    //                         if (count($android_tokens) > 0) {

    //                             $message_status = array();
    //                             $message_status[] = send_notification_android($android_tokens, $apnsArray);
    //                         }
    //                         // send notification to IOS Using FCM
    //                         if (count($ios_tokens) > 0) {

    //                             $message_status = array();
    //                             $message_status[] = send_notification_ios($ios_tokens, $apnsArray);

    //                         }
    //                          $notificationHistory = [
    //                             "sender_id" => $userId,
    //                             "user_id" => $userId,
    //                             "notification_content" => $notificationMessage,
    //                             "notification_type" => $notificationType,
    //                             "is_read" => "0",
    //                             "race_code" => $raceCode,
    //                             "race_id" =>  $raceId,
    //                             "response_data" => json_encode($apnsArray),
    //                             "created_date" => date("Y-m-d H:i:s"),
    //                             "updated_date" => date("Y-m-d H:i:s")
    //                         ];

    //                         $this->car->insertData('tbl_notifications', $notificationHistory);

    //                         $whereData = array(
    //                                         'race_status'=>'0',
    //                                         'race_time <='=> $before15Min,
    //                                         'is_notification_send' =>'0'
    //                                                 );
    //                         $this->car->updateData('tbl_create_race', $whereData, ['is_notification_send'=>'1']);
    //                     }
    //                 }

    //                 // ======================END NOTIFICATION================================ //
    // }

//cronNotificationDelete after 1month

    public function cronNotificationDelete()
    {
        $after1Month = date("Y-m-d",strtotime("-1 month"));
        // $currentTime=date("H:i:s",time());
        $array = array('DATE_FORMAT(created_date, "%Y-%m-%d") <='=> $after1Month);
        // print_r($array); die();
        $del = $this->car->deleteData('tbl_notifications', $array);
        //echo $this->db->last_query();die();
        if ($del) {
            echo "Del success";
        }else{
            echo "Not Del ";
        }
    }

    public function racerDisqualified()
    {
        date_default_timezone_set(API_TIME_ZONE);
        $currentDateTime = date("Y-m-d H:i:s", strtotime("-5 minutes"));
        
        $receiverUsers = $this->car->getJoinData('users.device_token,users.device_type,tbl_create_race.*, tbl_race_request.sender_id,tbl_race_request.receiver_id,tbl_race_request.race_id,tbl_race_request.id as raceRequestId', 'tbl_create_race', [['tbl_race_request', 'tbl_create_race.id = tbl_race_request.race_id'],['tbl_users users', 'users.id = tbl_race_request.receiver_id']], ['tbl_create_race.race_start_date <=' => $currentDateTime,'tbl_race_request.user_race_status' => '1','tbl_create_race.race_start_date IS NOT NULL' => null], null, null);
       // echo $this->db->last_query(); die;
        foreach($receiverUsers as $k => $user){
            $ios_tokens = array();
            $android_tokens = array();
            $notificationMessage = "You are disqualified from ". ucfirst($user->race_name).".";
            $notificationType = 9;
            
            $apnsArray = array(
                "sound"=>"default",
                "notificationType" => $notificationType,
                "userId" => $user->receiver_id,
                "raceCode" => $user->race_code,
                "raceId" =>  $user->id,
                "inputSpeedId" => (int)$user->input_speed_id,
                "raceTypeId" => (int)$user->race_type_id,
                "animationType" => (int)$user->animation_type,
                "time" =>  date("Y-m-d H:i:s"),
                "alert" => $notificationMessage,
                "body" => $notificationMessage
            );
            
            if ($user->device_type == '1') { //ios
                $ios_tokens [] = $user->device_token;
            } else if ($user->device_type == '2') {  //andriod
                $android_tokens [] = $user->device_token;
            }

            if (count($android_tokens) > 0) {
                // print_r($android_tokens);
                $message_status = array();
                $message_status[] = send_notification_android($android_tokens, $apnsArray);
            }
            // send notification to IOS Using FCM
            if (count($ios_tokens) > 0) {

                // print_r($ios_tokens);
                $message_status = array();
                $message_status[] = send_notification_ios($ios_tokens, $apnsArray);

            }
            
            $notificationHistory = [
                "sender_id" => 0,
                "user_id" => $user->receiver_id,
                "notification_content" => $notificationMessage,
                "notification_type" => $notificationType,
                "is_read" => "0",
                "race_code" => $user->race_code,
                "race_id" =>  $user->id,
                "input_speed_id" => (int)$user->input_speed_id,
                "race_type_id" => (int)$user->race_type_id,
                "animation_type" => (int)$user->animation_type,
                "created_date" => date("Y-m-d H:i:s"),
                "updated_date" => date("Y-m-d H:i:s")
            ]; 
            // print_r($notificationHistory); die();

            $this->car->insertData('tbl_notifications', $notificationHistory);

            $whereData = array(
                                'id'=>$user->id
                                    );
            // print_r($user->id); die();
            $this->car->updateData('tbl_create_race', $whereData, ['race_status'=>'2']);
            $this->car->updateData('tbl_race_request', ["id"=>$user->raceRequestId], ['user_race_status'=>'3']);
        }
    }

    public function updateRaceStatus()
    {
            $currentDate = date("Y-m-d",strtotime("-1 day"));
            // print_r($currentDate); die();
            $where = array(
                        'race_status'=>'0',
                        'race_date<='=> $currentDate,
                                );
            // print_r($where); die();
            $status = $this->car->updateData('tbl_create_race', $where, ['race_status'=>'3']);
            // echo $this->db->last_query(); die();
             if ($status) {
            echo "success";
            }else{
            echo "Not Del";
            }
    }

}
