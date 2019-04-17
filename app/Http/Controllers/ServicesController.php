<?php

namespace App\Http\Controllers;

use App\Models\ServicesModel;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\LoginModel;
use App\Models\GenericModel;
use App\Models\HelperModel;
use Illuminate\Http\Request;

use Log;

// use mysql_xdevapi\Exception;
// use App\Exception\Handler as ExceptionHandler;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use GuzzleHttp\Client;

class ServicesController extends Controller
{

    //type list for combo box
    function TypeList()
    {
        $val = GenericModel::simpleFetchGenericByWhere("proposal_type", "=", "IsActive", 1, "Id");

        $resultArray = json_decode(json_encode($val), true);
        $data = $resultArray;
        if (count($data) > 0) {
            return response()->json(['data' => $data, 'message' => 'Type fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Types not found'], 200);
        }
    }

    function TypeSubscriptionList(Request $request)
    {
        $userId = $request->input('userId');

        $val = ServicesModel::getSubscriptionList($userId);

        if($val["status"] == "success"){
            $resultArray = json_decode(json_encode($val["data"]), true);
            $data = $resultArray;

            if (count($data) > 0) {
                return response()->json(['data' => $data, 'message' => 'Type fetched successfully'], 200);
            } else {
                return response()->json(['data' => null, 'message' => 'Types not found'], 200);
            }

        }
        else{
            return response()->json(['data' => null, 'message' => 'Error Occur'], 500);

        }
        
        
    }

    function offer(Request $request)
    {
        error_log("offer");

        $apiKey = "24adb06e-787c-451c-8bd7-f8e02c09b919";
        $appName = "usabuyersgroup";

        $addr = $request->input('addr');
        $citystatezip = $request->input('citystatezip');

        if(empty($addr)){
            $addr = "";
        }
        if(empty($citystatezip)){
            $citystatezip = "";
        }

        try {

            // $client = new \GuzzleHttp\Client(['base_uri' => 'https://foo.com/api/']);
            // // Send a request to https://foo.com/api/test
            // $response = $client->request('GET', 'test');
            // // Send a request to https://foo.com/root
            // $response = $client->request('GET', '/root');




            // $data = $request->input('name');
            $client = new Client();
            $res = $client->request('Get', 'https://api.eppraisal.com/avm.json', [
                'query' => [
                    'apikey' => $apiKey,
                    'appname' => $appName,
                    'addr' => $addr,
                    'citystatezip' => $citystatezip,
                ]
            ]);
            error_log($res->getStatusCode());
            // echo $res->getStatusCode();
            // 200
            // error_log($res->getHeader('content-type'));
            // echo $res->getHeader('content-type');
            // 'application/json; charset=utf8'
            // error_log($res->getBody());
            // echo $res->getBody();

            // $res = $client->request('POST', 'http://www.exmple.com/mydetails', [
            //     'form_params' => [
            //         'name' => 'george',
            //     ]
            // ]);
        
            if ($res->getStatusCode() == 200) { // 200 OK
                $response_data = $res->getBody()->getContents();

                $data = json_decode($response_data, true);
                // return response()->json(['data' => $response_data, 'message' => 'success'], 200);
                return response()->json(['data' => $data, 'message' => 'success'], 200);
            }
            else{
                return response()->json(['data' => $response_data, 'message' => 'error occur'], $res->getStatusCode());

            }

            

        } catch (Exception $e) {
            error_log("error ".$e);

            return response()->json(['data' => null, 'message' => 'error occur'], 500);
        }

    }

    function invite(Request $request)
    {
        try {
            // $data = $request->input('name');
            $data = $request->all();

            $validator = ServicesController::inviteValidator($data);

            if ($validator->fails()) {
                return response()->json(['data' => $data, 'error' => $validator->errors(), 'message' => 'validation failed'], 400);
            } else {
                $check = ServicesModel::sendInviteTrans($request);

                if ($check['status'] == "success") {
                    return response()->json(['data' => true, 'message' => 'Invite is successfully sent'], 200);
                } else if ($check['status'] == "failed") {
                    return response()->json(['data' => false, 'message' => $check['message']], 400);
                } else {
                    return response()->json(['data' => false, 'message' => 'something went wrong'], 500);
                }
            }
        } catch (Exception $e) {
            return response()->json(['data' => null, 'message' => 'error occur'], 500);
        }

    }

    function inviteUpdate(Request $request)
    {
       try {
             $token = $request->input('Token');

            if ($token) {
                $check = ServicesModel::inviteUpdate($request);

                if ($check['status'] == "success") {
                    // return response()->json(['data' => $check['data'], 'message' => 'Successfully Login'], 200);
                    // return response()->json(['data' => true, 'message' => $check['message']], 200);
                    return response()->json(['data' => $check['data'], 'message' => $check['message']], 200);
                } else if ($check['status'] == "failed") {
                    return response()->json(['data' => false, 'message' => $check['message']], 400);
                }
                else {
                    return response()->json(['data' => false, 'message' => 'something went wrong'], 500);
                }

            } else {
                return response()->json(['data' => null, 'message' => 'token not found'], 400);
            }
       } catch (Exception $e) {
           return response()->json(['data' => null, 'message' => 'error occur'], 500);
       }

    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function inviteValidator(array $data)
    {
        return Validator::make($data, [
            // 'EmailAddress' => ['required', 'string', 'email', 'max:255', 'unique:user'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'mobileNumber' => [],
            'type' => ['required', 'string'],
        ]);
    }


}
