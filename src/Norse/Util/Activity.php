<?php namespace Norse\Util;

class Activity {
    private static $user = null;
    private static $accountId = null;

    /**
     * @param $user
     */
    public static function setUser($user){
        self::$user = $user;
    }

    /**
     * @param $accountId
     */
    public static function setAccount($accountId){
        self::$accountId = $accountId;
    }

    /**
     * @param $action
     * @param $actionDetails
     */
    public static function log($action, $actionDetails, $group, $ip=false)
    {
        $data = array(
            'ts' => time(),
            'user_id' =>  self::$user->user_id,
            'user_name' =>  self::$user->name,
            'action' => $action,
            'action_details' => $actionDetails,
            '`group`' => $group
        );
        if ($ip) { $data["ip"] = ip2long($ip); }
        DB::getInstance('CM')->insertRows(array($data), 'cm_'.self::$accountId.'.activities');
    }
}