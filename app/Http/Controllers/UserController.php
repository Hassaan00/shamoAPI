<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use View;
use App\Models\UserModel;
use App\Models\ServicesModel;
use App\Models\GenericModel;
use App\Models\HelperModel;
use Config;
use Carbon\Carbon;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //    Input::merge(array_map('trim', Input::except('selectedRoles')));
        //   $validationRules = UserController::getValidateRules();
        //  $validator = Validator::make($request->all(), $validationRules);

        $redirectUserForm = url('/user/add/0');
        $redirectUser = url('/');

        //      if ($validator->fails())
        //           return redirect($redirectUserForm)->withErrors($validator)->withInput(Input::all());


        $isInserted = UserModel::addUser();
        //   if ($isInserted == 'unmatchPassword')
        //        return redirect($redirectUserForm)->withErrors(['confirm password must match the password']);
        if ($isInserted == 'duplicate')
            return redirect($redirectUserForm)->withErrors(['Duplication Error! This First Name and Last Name is already exist'])->withInput(Input::all());
        else if ($isInserted == 'success')
            return redirect($redirectUser)->with(['success' => Config::get('settings.form_save_success_message')]);
        else
            return redirect($redirectUser)->withErrors([Config::get('settings.form_save_failed_message')]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function storePatient(Request $request)
    {

        $redirectUserForm = url('/admin/home');
        $redirectUser = url('/admin/home');
        $isInserted = UserModel::addPatient();
        //   if ($isInserted == 'unmatchPassword')addPatient
        //        return redirect($redirectUserForm)->withErrors(['confirm password must match the password']);
        if ($isInserted == 'duplicate')
            return redirect($redirectUserForm)->withErrors(['Duplication Error! This First Name and Last Name is already exist'])->withInput(Input::all());
        else if ($isInserted == 'success')
            return redirect($redirectUser)->with(['success' => Config::get('settings.form_save_success_message')]);
        else
            return redirect($redirectUser)->withErrors([Config::get('settings.form_save_failed_message')]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        Input::merge(array_map('trim', Input::except('selectedRoles')));
        $validationRules = UserController::getValidateRulesForUpdate();
        $validator = Validator::make($request->all(), $validationRules);
        $id = $request->input('userID');

        $redirectUserForm = url('/user_form/update/' . $id);
        $redirectUser = url('/user');

        if ($validator->fails())
            return redirect($redirectUserForm)->withErrors($validator)->withInput(Input::all());


        $isUpdated = UserModel::updateUser($request);
        if ($isUpdated == 'duplicate')
            return redirect(url('/user_form/update/' . $id))->withErrors(['Duplication Error! This First Name and Last Name is already exist']);
        else if ($isUpdated == 'success' && HelperModel::getUserSessionID() != $id)
            return redirect($redirectUser)->with(['success' => Config::get('settings.form_update_success_message ')]);
        else if ($isUpdated == 'success' && HelperModel::getUserSessionID() == $id)
            return redirect($redirectUser)->with(['success' => Config::get('settings.form_update_success_message ') . '.Login again to see changes']);
        else
            return redirect($redirectUser)->withErrors([Config::get('settings.form_update_failed_message')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $genericModel1 = new GenericModel;
        $row1 = $genericModel1->deleteGeneric('userrole', 'UserID', $id);
        $genericModel = new GenericModel;
        $row = $genericModel->deleteGeneric('user', 'UserID', $id);

        if ($row > 0 && $row1 > 0)
            return redirect(url('/user'))->with(['success' => Config::get('settings.form_delete_success_message')]);
        else
            return redirect(url('/user'))->with(['success' => Config::get('settings.form_delete_failed_message')]);
        //
    }

    public function lock($id)
    {
        $result = UserModel::find($id);
        if (isset($result)) {
            $fetchresult = json_decode(json_encode($result[0]), true);
//            echo ' '.$fetchresult['Status'].' ';
            $row = UserModel::lock($id, $fetchresult);

            if ($row == 'success' && HelperModel::getUserSessionID() != $id) {
                return redirect(url('/user'))->with(['success' => 'Lock status successfully changed']);
            } else if ($row == 'success' && HelperModel::getUserSessionID() == $id) {
                return redirect(url('/user'))->with(['success' => 'Lock status successfully changed, login again to see changes']);
            } else {
                return redirect(url('/user'))->with(['success' => 'Lock status failed to changed']);
            }
        } else {
            return "Problem in fetching data in view";
        }
    }

    public function find()
    {
        return UserModel::searchUser();
    }

    private function getValidateRules()
    {
        $rules = array('firstName' => 'required|alpha|min:2|max:25',
            'lastName' => 'required|alpha|min:2|max:25',
            'password' => ['required', 'regex:/^(?=.*[a-zA-Z])(?=.*[-=!@#$%^&*_<>?|,.;:\(){}]).{8,}$/'],
            'confirmPassword' => ['required', 'regex:/^(?=.*[a-zA-Z])(?=.*[-=!@#$%^&*_<>?|,.;:\(){}]).{8,}$/'],
            'email' => 'required|email');

        return $rules;
    }

    private function getValidateRulesForUpdate()
    {
        $rules = array('firstName' => 'required|alpha|min:2|max:25',
            'lastName' => 'required|alpha|min:2|max:25',
            'email' => 'required|email');

        return $rules;
    }

    //user list via pagination
    function UserListViaPagination(Request $request)
    {

        error_log('in controller');

        $pageNo = $request->input('pageNo');
        $limit = $request->input('limit');
        $keyword = $request->input('search');

        $val = UserModel::UserListViaPagination
        ('user', $pageNo, $limit, 'Id', $keyword);

        $data = $val;
        error_log(count($data));
        if (count($data) > 0) {
            return response()->json(['data' => $data, 'message' => 'Users fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Users not found'], 200);
        }
    }

    //user list for combo box

    function UserList()
    {

        $val = UserModel::getUserList();

        $resultArray = json_decode(json_encode($val), true);
        $data = $resultArray;
        if (count($data) > 0) {
            return response()->json(['data' => $data, 'message' => 'Users fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Users not found'], 200);
        }
    }

    function GetUserViaRoleCode(Request $request)
    {
        $roleCode = $request->get('roleCode');

        $val = UserModel::GetUserViaRoleCode($roleCode);
        $userData = array();
        foreach ($val as $item) {
            $data = array(
                'Id' => $item->Id,
                'FirstName' => $item->FirstName,
                'LastName' => $item->LastName,
                'EmailAddress' => $item->EmailAddress,
                'MobileNumber' => $item->MobileNumber,
                'TelephoneNumber' => $item->TelephoneNumber,
                'Gender' => $item->Gender,
                'FunctionalTitle' => $item->FunctionalTitle,
                'Role' => array()
            );

            $data['Role']['Id'] = $item->RoleId;
            $data['Role']['Name'] = $item->RoleName;
            $data['Role']['CodeName'] = $item->RoleCodeName;

            array_push($userData, $data);
        }

        if (count($val) > 0) {
            return response()->json(['data' => $userData, 'message' => 'Users fetched successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Users not found'], 200);
        }
    }

    //user list count API

    function UserCount(Request $request)
    {

        error_log('in controller');
        $keyword = $request->input('search');
        $val = UserModel::UserListCount($keyword);

        return response()->json(['data' => $val, 'message' => 'Users count'], 200);
    }

    function UserChangePassword(Request $request)
    {
        $id = $request->get('Id');
        $currentPassword = $request->get('CurrentPassword');
        $newPassword = $request->get('NewPassword');

        //First get and check if record exists or not
        $data = UserModel::GetSingleUserViaId($id);

        if (count($data) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        } //
        else {

            $currentHashPass = md5($currentPassword);
            $newHashPass = md5($newPassword);

            if ($currentHashPass != $data[0]->Password) {
                return response()->json(['data' => null, 'message' => 'Invalid current password'], 400);
            } else {

                if ($currentHashPass == $newHashPass) {
                    return response()->json(['data' => null, 'message' => 'You entered an old password'], 400);
                } else {

                    //Binding data to variable.

                    $dataToUpdate = array(
                        "Password" => $newHashPass,
                    );

                    $update = GenericModel::updateGeneric('user', 'Id', $id, $dataToUpdate);

                    if ($update == true) {
                        DB::commit();


                        $emailAddress = $data[0]->EmailAddress;
                        $emailMessage = "Your password has been updated.";


                        UserModel::sendEmail($data[0]->EmailAddress, $emailMessage, null);
                        return response()->json(['data' => null, 'message' => 'Password successfully updated'], 200);
                    } else {
                        DB::rollBack();
                        return response()->json(['data' => null, 'message' => 'Error in updating updating password'], 400);
                    }

                }


            }

        }


    }

    function UserUpdateBasic(Request $request)
    {
        $id = $request->get('id');

        //First get and check if record exists or not
        $data = UserModel::GetSingleUserViaId($id);

        if (count($data) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        }

        //We have get the data.
        //Now insert that data in log table to maitain old record of that user
        $date = HelperModel::getDate();


        DB::beginTransaction();


        //Binding data to variable.

        $firstName = $request->post('FirstName');
        $lastName = $request->post('LastName');
        $mobileNumber = $request->post('MobileNumber');
        $telephoneNumber = $request->post('TelephoneNumber');
        $officeAddress = $request->post('OfficeAddress');
        $residentialAddress = $request->post('ResidentialAddress');
        $gender = $request->post('Gender');
        $functionalTitle = $request->post('FunctionalTitle');
        $age = $request->post('Age');
        $ageGroup = $request->post('AgeGroup');

        $date = HelperModel::getDate();

        $dataToUpdate = array(
            "FirstName" => $firstName,
            "LastName" => $lastName,
            "MobileNumber" => $mobileNumber,
            "TelephoneNumber" => $telephoneNumber,
            "OfficeAddress" => $officeAddress,
            "ResidentialAddress" => $residentialAddress,
            "Gender" => $gender,
            "FunctionalTitle" => $functionalTitle,
            "Age" => $age,
            "AgeGroup" => $ageGroup,
            'UpdatedOn' => $date["timestamp"]

        );
        $emailMessage = "Dear User <br/>Update is made on your records";

        $update = GenericModel::updateGeneric('user', 'Id', $id, $dataToUpdate);

        if ($update == true) {
            DB::commit();
            UserModel::sendEmail($data[0]->EmailAddress, $emailMessage, null);
            return response()->json(['data' => null, 'message' => 'User successfully updated'], 200);
        } else {
            DB::rollBack();
            return response()->json(['data' => null, 'message' => 'Error in updating user record'], 400);
        }
    }

    function GetSingleUserViaId(Request $request)
    {
        $id = $request->get('id');

//        $doctorRole = env('ROLE_DOCTOR');

        $val = UserModel::GetSingleUserViaIdNewFunction($id);

        if ($val != null) {

            $userDetails = array();

            $userDetails['Id'] = $val->Id;
            $userDetails['FirstName'] = $val->FirstName;
            $userDetails['LastName'] = $val->LastName;
            $userDetails['EmailAddress'] = $val->EmailAddress;
            $userDetails['MobileNumber'] = $val->MobileNumber;
            $userDetails['TelephoneNumber'] = $val->TelephoneNumber;
            $userDetails['OfficeAddress'] = $val->OfficeAddress;
            $userDetails['ResidentialAddress'] = $val->ResidentialAddress;
            $userDetails['Gender'] = $val->Gender;
            $userDetails['FunctionalTitle'] = $val->FunctionalTitle;
            $userDetails['Age'] = $val->Age;
            $userDetails['AgeGroup'] = $val->AgeGroup;
            $userDetails['IsBlock'] = $val->IsBlock;
            $userDetails['BlockReason'] = $val->BlockReason;
            $userDetails['Role'] = array();
            $userDetails['Role']['Id'] = $val->RoleId;
            $userDetails['Role']['RoleName'] = $val->RoleName;
            $userDetails['Role']['RoleCodeName'] = $val->RoleCodeName;
            $userDetails['Subscription'] = array();

            $subVal = ServicesModel::getSubscriptionList($id);

            if ($subVal["status"] == "success") {
                $resultArray = json_decode(json_encode($subVal["data"]), true);
                $userDetails['Subscription'] = $resultArray;
            }

            // $data = array();
            // //Pushing logged in user basic inforamtion
            // array_push($data, $val);

            if ($userDetails != null) {
                return response()->json(['data' => $userDetails, 'message' => 'User detail fetched successfully'], 200);
            } else {
                return response()->json(['data' => null, 'message' => 'User detail not found'], 200);
            }
        } else {
            return response()->json(['data' => null, 'message' => 'User detail not found'], 400);
        }
    }

    function UserRegistration(Request $request)
    {
        error_log('In controller');

        $emailAddress = $request->post('EmailAddress');
        //First get and check if email record exists or not
        $checkEmail = UserModel::isDuplicateEmail($emailAddress);

        error_log('Checking email bit' . $checkEmail);

        if (count($checkEmail) > 0) {
            return response()->json(['data' => null, 'message' => 'Email already exists'], 400);
        }

        $defaultPassword = getenv("DEFAULT_PWD");

        //Binding data to variable.
        $firstName = $request->get('FirstName');
        $lastName = $request->get('LastName');
        $mobileNumber = $request->get('MobileNumber');
        $telephoneNumber = $request->get('TelephoneNumber');
        $officeAddress = $request->get('OfficeAddress');
        $residentialAddress = $request->get('ResidentialAddress');
        $gender = $request->get('Gender');
        $functionalTitle = $request->get('FunctionalTitle');
        $age = $request->get('Age');
        $ageGroup = $request->get('AgeGroup');
        $hashedPassword = md5($defaultPassword);
        $userSubscription = $request->get('Subscription');

        $roleData = UserModel::getRoleViaRoleCode("system_administrator");

        if (count($roleData) == 0) {
            DB::rollback();
            return response()->json(['data' => null, 'message' => 'Role not found'], 400);
        }
        $roleId = $roleData[0]->Id;
        $roleName = $roleData[0]->Name;

        error_log('$roleId' . $roleId);

        $date = HelperModel::getDate();

        $dataToInsert = array(
            "EmailAddress" => $emailAddress,
            "FirstName" => $firstName,
            "LastName" => $lastName,
            "MobileNumber" => $mobileNumber,
            "TelephoneNumber" => $telephoneNumber,
            "OfficeAddress" => $officeAddress,
            "ResidentialAddress" => $residentialAddress,
            "Password" => $hashedPassword,
            "Gender" => $gender,
            "FunctionalTitle" => $functionalTitle,
            "Age" => $age,
            "AgeGroup" => $ageGroup,
            "IsActive" => true,
            "AccountVerified" => true,
            "IsBlock" => false,
            "CreatedOn" => $date["timestamp"]
        );

        DB::beginTransaction();

        $insertedRecord = GenericModel::insertGenericAndReturnID('user', $dataToInsert);
        error_log('Inserted record id ' . $insertedRecord);

        if ($insertedRecord == 0) {
            DB::rollback();
            return response()->json(['data' => null, 'message' => 'Error in user registration'], 400);
        }

        //Now making data for user_access
        $userAccessData = array(
            "UserId" => $insertedRecord,
            "RoleId" => $roleId,
            "IsActive" => true
        );

        $insertUserAccessRecord = GenericModel::insertGenericAndReturnID('user_access', $userAccessData);

        if ($insertUserAccessRecord == 0) {
            DB::rollback();
            return response()->json(['data' => null, 'message' => 'Error in user assigning role'], 400);
        } else {

            $userSubscriptionData = array(
                "UserId" => $insertedRecord,
                "StartDate" => $userSubscription["StartDate"],
                "EndDate" => $userSubscription["EndDate"],
                "CreatedOn" => $date["timestamp"],
                "IsActive" => true
            );

            $insertSubscriptionRecord = GenericModel::insertGenericAndReturnID('user_subscription', $userSubscriptionData);

            if ($insertSubscriptionRecord == 0) {
                DB::rollback();
                return response()->json(['data' => null, 'message' => 'Error in adding user subscription'], 400);
            } else {
                error_log('user subscription inserted');

                if (count($userSubscription["SubscriptionDetail"]) > 0) {
                    $subDetailData = array();
                    foreach ($userSubscription["SubscriptionDetail"] as $item) {

                        $data = array(
                            'SubscriptionId' => $insertSubscriptionRecord,
                            'ProposalTypeId' => (int)$item['ProposalTypeId'],
                            'UserId' => $insertedRecord,
                            'CreatedOn' => $date["timestamp"],
                            'IsActive' => true
                        );

                        array_push($subDetailData, $data);
                    }

                    $userSubscriptionDetailInsert = GenericModel::insertGeneric('user_subscription_detail', $subDetailData);

                    if ($userSubscriptionDetailInsert == 0) {
                        DB::rollback();
                        return response()->json(['data' => null, 'message' => 'Error in adding user subscription details'], 400);
                    }
                }

                error_log('user subscription detail inserted');

                $emailMessage = "Welcome, You are successfully registered to ROS as ' .$roleName. ', use this password to login ' . $defaultPassword";


                DB::commit();
                //Now sending email
                UserModel::sendEmail($emailAddress, $emailMessage, null);

                return response()->json(['data' => $insertedRecord, 'message' => 'User successfully registered'], 200);
            }
        }
    }

    function UpdateUserFullInformation(Request $request)
    {
        error_log('In controller');

        $id = $request->get('id');


//        $emailAddress = $request->post('EmailAddress');
//        //First get and check if email record exists or not
//        $checkEmail = UserModel::isDuplicateEmail($emailAddress);
//
//        error_log('Checking email bit' . $checkEmail);
//
//        if (count($checkEmail) > 0) {
//            return response()->json(['data' => null, 'message' => 'Email already exists'], 400);
//        }

        error_log('id is : ' . $id);

        $userData = UserModel::GetSingleUserViaIdNewFunction($id);
        if ($userData == null) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        }


        //Binding data to variable.
        $firstName = $request->get('FirstName');
        $lastName = $request->get('LastName');
        $mobileNumber = $request->get('MobileNumber');
        $telephoneNumber = $request->get('TelephoneNumber');
        $officeAddress = $request->get('OfficeAddress');
        $residentialAddress = $request->get('ResidentialAddress');
        $gender = $request->get('Gender');
        $functionalTitle = $request->get('FunctionalTitle');
        $age = $request->get('Age');
        $ageGroup = $request->get('AgeGroup');
        $userSubscription = $request->get('Subscription');

        $date = HelperModel::getDate();

        $dataToUpdate = array(
//            "EmailAddress" => $emailAddress,
            "FirstName" => $firstName,
            "LastName" => $lastName,
            "MobileNumber" => $mobileNumber,
            "TelephoneNumber" => $telephoneNumber,
            "OfficeAddress" => $officeAddress,
            "ResidentialAddress" => $residentialAddress,
            "Gender" => $gender,
            "FunctionalTitle" => $functionalTitle,
            "Age" => $age,
            "AgeGroup" => $ageGroup,
            "UpdatedOn" => $date["timestamp"]
        );

        DB::beginTransaction();

        $updateRecord = GenericModel::updateGeneric('user', 'Id', $id, $dataToUpdate);
        error_log('Updated record  ' . $updateRecord);

        if ($updateRecord == 0) {
            DB::rollback();
            return response()->json(['data' => null, 'message' => 'Error in updating user information'], 400);
        }

        $userSubscriptionData = array(
            "StartDate" => $userSubscription["StartDate"],
            "EndDate" => $userSubscription["EndDate"],
            "UpdatedOn" => $date["timestamp"]
        );

        $getSubscriptionRecord = UserModel::GetSubscriptionRecord($id);
        if ($getSubscriptionRecord == null) {
            DB::rollback();
            return response()->json(['data' => null, 'message' => 'Subscription record not found'], 400);
        }

        $updateSubscriptionRecord = GenericModel::updateGeneric('user_subscription', 'UserId', $id, $userSubscriptionData);

        if ($updateSubscriptionRecord == 0) {
            DB::rollback();
            return response()->json(['data' => null, 'message' => 'Error in adding user subscription'], 400);
        } else {
            error_log('user subscription updated');
            error_log('Now fetch user subscription details via user id and remove them if exists');

            $gteUserSubscriptionDetail = UserModel::GetSubscriptionDetails($id);
            if (count($gteUserSubscriptionDetail) > 0) {
                $result = GenericModel::deleteGeneric('user_subscription_detail', 'UserId', $id);
                if ($result == false) {
                    DB::rollBack();
                    return response()->json(['data' => null, 'message' => 'Error in deleting schedule shift'], 400);
                }
            }

            error_log('records deleted');

            if (count($userSubscription["SubscriptionDetail"]) > 0) {
                $subDetailData = array();
                foreach ($userSubscription["SubscriptionDetail"] as $item) {

                    $data = array(
                        'SubscriptionId' => $getSubscriptionRecord->Id,
                        'ProposalTypeId' => (int)$item['ProposalTypeId'],
                        'UserId' => $id,
                        'CreatedOn' => $date["timestamp"],
                        'IsActive' => true
                    );

                    array_push($subDetailData, $data);
                }

                $userSubscriptionDetailInsert = GenericModel::insertGeneric('user_subscription_detail', $subDetailData);

                if ($userSubscriptionDetailInsert == 0) {
                    DB::rollback();
                    return response()->json(['data' => null, 'message' => 'Error in adding user subscription details'], 400);
                }
            }

            error_log('user subscription detail inserted');


            DB::commit();

            return response()->json(['data' => (int)$id, 'message' => 'User successfully updated'], 200);
        }
    }

    function UserDelete(Request $request)
    {
        error_log('in controller');
        $id = $request->get('id');

        //First get and check if record exists or not
        $getUser = UserModel::getUserViaId($id);

        if (count($getUser) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        }
        //Binding data to variable.
        $dataToUpdate = array(
            "IsActive" => false
        );

        $update = GenericModel::updateGeneric('user', 'Id', $id, $dataToUpdate);

        //now delete the account_invitation
        //of this email

        $updateAccountInvitation = GenericModel::updateGeneric('account_invitation', 'ToEmailAddress', $getUser[0]->EmailAddress, $dataToUpdate);

        if ($update == true) {
            return response()->json(['data' => $id, 'message' => 'Deleted successfully'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Error in deleting'], 400);
        }
    }

    function SuperAdminDashboard(Request $request)
    {
        error_log('in controller');

        $superAdminRole = env('ROLE_SUPER_ADMIN');
        $doctorRole = env('ROLE_DOCTOR');
        $facilitatorRole = env('ROLE_FACILITATOR');
        $supportStaffRole = env('ROLE_SUPPORT_STAFF');
        $patientRole = env('ROLE_PATIENT');

        $superAdminCount = UserModel::getUserCountViaRoleCode($superAdminRole);
        $doctorCount = UserModel::getUserCountViaRoleCode($doctorRole);
        $facilitatorCount = UserModel::getUserCountViaRoleCode($facilitatorRole);
        $supperStaffCount = UserModel::getUserCountViaRoleCode($supportStaffRole);
        $patientCount = UserModel::getUserCountViaRoleCode($patientRole);

        $data = array(
            "SuperAdmin" => $superAdminCount,
            "Doctor" => $doctorCount,
            "Facilitator" => $facilitatorCount,
            "SupportStaff" => $supperStaffCount,
            "Patient" => $patientCount
        );

        return response()->json(['data' => $data, 'message' => 'Role wise user count'], 200);
    }

    function GetUserInvitationListWithPaginationAndSearch(Request $request)
    {
        error_log('In controller');

        $pageNo = $request->get('p');
        $limit = $request->get('c');
        $searchKeyword = $request->get('s');

        $data = UserModel::getUserInvitationLink($pageNo, $limit, $searchKeyword);

        error_log('Count of data is : ' . count($data));

        if (count($data) > 0) {
            return response()->json(['data' => $data, 'message' => 'User invitation list found'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'User invitation list not found'], 200);
        }
    }

    function GetUserInvitationListCount(Request $request)
    {
        error_log('In controller');

        $searchKeyword = $request->get('s');

        $data = UserModel::getUserInvitationLinkCount($searchKeyword);

        return response()->json(['data' => $data, 'message' => 'User invitation count'], 200);
    }

    function UserBlock(Request $request)
    {
        error_log('in controller');
        $id = $request->get('id');

        //First get and check if record exists or not
        $data = UserModel::GetSingleUserViaId($id);

        if (count($data) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        }

        if ($data[0]->IsBlock == true) {
            return response()->json(['data' => null, 'message' => 'User is already blocked'], 400);
        }

        //Binding data to variable.

        $dataToUpdate = array(
            "IsBlock" => true,
            "BlockReason" => $request->get('BlockReason')
        );

        $update = GenericModel::updateGeneric('user', 'Id', $id, $dataToUpdate);

        if ($update == true) {
            return response()->json(['data' => $id, 'message' => 'User successfully blocked'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Error in blocking user'], 400);
        }
    }

    function UserUnblock(Request $request)
    {
        error_log('in controller');
        $id = $request->get('id');

        //First get and check if record exists or not
        $data = UserModel::GetSingleUserViaId($id);

        if (count($data) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        }

        if ($data[0]->IsBlock == false) {
            return response()->json(['data' => null, 'message' => 'User is already unblocked'], 400);
        }

        //Binding data to variable.

        $dataToUpdate = array(
            "IsBlock" => false
        );

        $update = GenericModel::updateGeneric('user', 'Id', $id, $dataToUpdate);

        if ($update == true) {
            return response()->json(['data' => $id, 'message' => 'User successfully unblocked'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Error in unblocking user'], 400);
        }
    }


    function PermissionViaRoleId(Request $request)
    {
        error_log('in controller');

        $roleId = $request->get('RoleId');

        $result = UserModel::getPermissionViaRoleId($roleId);
        if (count($result) > 0) {
            return response()->json(['data' => $result, 'message' => 'Permission successfully fetched'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Permission not found'], 400);
        }
    }

    function PermissionViaUserId(Request $request)
    {
        error_log('in controller');

        $userId = $request->get('UserId');

        $data = UserModel::GetUserRoleViaUserId($userId);
        if (count($data) == 0) {
            return response()->json(['data' => null, 'message' => 'User has not yet assigned with any role'], 400);
        }
        $roleId = $data[0]->RoleId;

        error_log('$roleId' . $roleId);

        $result = UserModel::getPermissionViaRoleId($roleId);

        if (count($result) > 0) {
            return response()->json(['data' => $result, 'message' => 'Permission successfully fetched'], 200);
        } else {
            return response()->json(['data' => null, 'message' => 'Permission not found'], 400);
        }
    }


    function AssociateFacilitatorsWithDoctor(Request $request)
    {
        error_log('In controller');

        $doctorFacilitatorAssociation = env('ASSOCIATION_DOCTOR_FACILITATOR');
        $doctorRole = env('ROLE_DOCTOR');

        $doctorId = $request->DoctorId;
        $facilitators = $request->Facilitator;

        //First check if this doctor is belonging to role doctor or not
        $doctorsData = UserModel::GetSingleUserViaId($doctorId);
        if (count($doctorsData) == 0) {
            return response()->json(['data' => null, 'message' => 'User not found'], 400);
        } else {
            if ($doctorsData[0]->RoleCodeName != $doctorRole) {
                return response()->json(['data' => null, 'message' => 'Logged in user is not doctor'], 400);
            }
        }

        DB::beginTransaction();

        //First get the record of role permission with respect to that given role id
        $checkDoctorFacilitator = UserModel::getDestinationUserIdViaLoggedInUserIdAndAssociationType($doctorId, $doctorFacilitatorAssociation);
        error_log('$checkDoctorFacilitator ' . $checkDoctorFacilitator);
        //Now check the permission if it exists
        if (count($checkDoctorFacilitator) > 0) {
            //then delete it from role_permission
            $result = UserModel::deleteAssociatedFacilitators($doctorId, $doctorFacilitatorAssociation);
            if ($result == false) {
                DB::rollBack();
            }
        }
        $userIds = array();
        $data = array();

        foreach ($facilitators as $item) {
            array_push
            (
                $data,
                array(
                    "SourceUserId" => $doctorId,
                    "DestinationUserId" => $item['Id'],
                    "AssociationType" => $doctorFacilitatorAssociation,
                    "IsActive" => true
                )
            );

            array_push($userIds, $item['Id']);
        }

        //Now get all facilitator email address
        //And then shoot email to them that they are now associated with XYZ dr.

        $getFacilitatorEmails = UserModel::getMultipleUsers($userIds);
        if (count($getFacilitatorEmails) == 0) {
            return response()->json(['data' => null, 'message' => 'Facilitator(s) not found'], 400);
        }


        //Now inserting data
        $checkInsertedData = GenericModel::insertGeneric('user_association', $data);
        error_log('$checkInsertedData ' . $checkInsertedData);

        if ($checkInsertedData == true) {
            DB::commit();

            $emailMessage = "You have been associated with Dr. " . $doctorsData[0]->FirstName . ".";

            error_log($emailMessage);

            error_log(count($getFacilitatorEmails));

            $toNumber = array();
            $phoneCode = getenv("PAK_NUM_CODE");//fetch from front-end

            foreach ($getFacilitatorEmails as $item) {

                //pushing mobile number
                //in array for use in sending sms
                array_push($toNumber, $phoneCode . $item->MobileNumber);

                error_log('$item' . $item->EmailAddress);
                error_log('$item' . $item->MobileNumber);

                UserModel::sendEmail($item->EmailAddress, $emailMessage, null);
            }

            ## Preparing Data for SMS  - START ##
            if (count($toNumber) > 0) {
                HelperModel::sendSms($toNumber, $emailMessage, null);
            }
            ## Preparing Data for SMS  - END ##

            return response()->json(['data' => $doctorId, 'message' => 'Facilitator(s) successfully associated'], 200);

        } else {
            DB::rollBack();
            return response()->json(['data' => null, 'message' => 'Error in associating facilitator(s)'], 400);
        }
    }

    function GetAssociateFacilitator(Request $request)
    {
        error_log('in controller');

        $userId = $request->get('doctorId');
        $doctorFacilitatorAssociation = env('ASSOCIATION_DOCTOR_FACILITATOR');

        $data = UserModel::GetUserRoleViaUserId($userId);
        if (count($data) == 0) {
            return response()->json(['data' => null, 'message' => 'User data not found'], 400);
        }

        $getAssociatedFacilitators = UserModel::getDestinationUserIdViaLoggedInUserIdAndAssociationType($userId, $doctorFacilitatorAssociation);
        if (count($getAssociatedFacilitators) == 0) {
            return response()->json(['data' => null, 'message' => 'Facilitator not associated yet'], 400);
        } else {
            $getAssociatedFacilitatorIds = array();
            foreach ($getAssociatedFacilitators as $item) {
                array_push($getAssociatedFacilitatorIds, $item->DestinationUserId);
            }
            $getAssociatedFacilitatorData = UserModel::getMultipleUsers($getAssociatedFacilitatorIds);

            if (count($getAssociatedFacilitatorData) > 0) {
                return response()->json(['data' => $getAssociatedFacilitatorData, 'message' => 'Associated facilitators fetched successfully'], 200);
            } else {
                return response()->json(['data' => null, 'message' => 'No Facilitator associated with the Doctor'], 200);
            }
        }
    }
}
