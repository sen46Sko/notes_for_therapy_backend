<?php
namespace App\Helper;

class Helper
{
    function sendPushNotification($title,$message,$fcm_token,$id=null) {

        $url = "https://fcm.googleapis.com/fcm/send";
        $key = 'AAAAbUsnFP8:APA91bHPNqIwA81LlfXZE3BDaKGWsZhnYb-pAoJ-pK8nc8-SZiGynGbZ9vlaYlhGsywTMACCq7vu0lXBtIaO7tqUuzk6han7uY7PI_lnqSc2QbDqGvXeedOpdmAycVIsl2whzEPOaQlj';
        $header = [
            'authorization: key=' . $key,
            'content-type: application/json'
        ];

        $postdata = '{
            "to" : "'.$fcm_token.'",
                "notification" : {
                    "title":"' . $title . '",
                    "body" : "' . $message . '"
                },
            "data" : {
                "id" : "'.$id.'",
                "title":"' . $title . '",
                "description" : "' . $message . '",
                "text" : "' . $message . '",
                "is_read": 0
              }
        }';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}