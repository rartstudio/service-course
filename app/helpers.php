<?php

use Illuminate\Support\Facades\Http;

function getUser($userId){
    $url = env('SERVICE_USER_URL').'users/'.$userId;

    try {
        $response = Http::timeout(10)->get($url);
        $data = $response->json();
        $data['http_code'] = $response->getStatusCode();
        return $data;
    } catch (\Throwable $th){
        return [
            'status' => 'error',
            'http_code' => 500,
            'message' => 'service user unavailable'
        ];
    }
}

function getUserByIds($userIds = []){
    $url = env('SERVICE_USER_URL').'users/';

    try {
        if(count($userIds) === 0){
            return [
                'status' => 'success',
                'http_code' => 200,
                'data' => []
            ];
        }

        //using array cause we take more than one input id from user ex [2,4,3]
        $response = Http::timeout(10)->get($url, ['user_ids[]' => $userIds]);
        $data = $response->json();
        $data['http_code'] = $response->getStatusCode();
        return $data;
    } catch (\Throwable $th){
        return [
            'status' => 'error',
            'http_code' => 500,
            'message' => 'service user unavailable'
        ];
    }
}